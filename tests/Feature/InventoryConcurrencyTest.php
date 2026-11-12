<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderConfirmationMailer;
use App\Services\OrderWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class InventoryConcurrencyTest extends TestCase
{
    use CreatesAccountTables;
    use CreatesDeliveryFeeTable;
    use CreatesMenuTables;
    use CreatesOrderTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAccountTables();
        $this->createDeliveryFeeTable();
        $this->createMenuTables();
        $this->createOrderTables();
        $this->withoutVite();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->byDefault();
            $mock->shouldReceive('sendCancelled')->byDefault();
        });
    }

    /**
     * Requirement 1: Successful purchase when stock is available.
     */
    public function test_successful_purchase_when_stock_is_available(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 2);
        $paymentMethodId = $this->createPaymentMethod();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ]);

        $response->assertRedirect(route('account'));

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 3,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->user_id,
            'order_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $productId,
            'quantity' => 2,
        ]);
    }

    /**
     * Requirement 2: Checkout rejection when stock is insufficient.
     */
    public function test_checkout_rejection_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 1);
        $this->addCartItem($user, $productId, quantity: 2);
        $paymentMethodId = $this->createPaymentMethod();

        $response = $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Mango Street',
                'barangay' => 'Poblacion',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'payment_method_id' => $paymentMethodId,
            ]);

        $response->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors([
                'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
            ]);

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 1,
        ]);

        $this->assertDatabaseMissing('orders', [
            'user_id' => $user->user_id,
        ]);
    }

    /**
     * Requirement 3: Two customers attempting to purchase the last available item simultaneously.
     * Only one must succeed; the other receives an insufficient-stock error. Final stock must be 0.
     */
    public function test_two_customers_attempting_to_purchase_last_available_item_simultaneously(): void
    {
        $customerA = User::factory()->create();
        $customerB = User::factory()->create();

        $productId = $this->createProduct(stock: 1);
        $this->addCartItem($customerA, $productId, quantity: 1);
        $this->addCartItem($customerB, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();

        // Customer A submits checkout first
        $responseA = $this->actingAs($customerA)->post(route('checkout.store'), [
            'recipient_name' => 'Customer A',
            'phone_number' => '09171111111',
            'address_line' => '101 First Ave',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ]);

        $responseA->assertRedirect(route('account'));

        // Product stock is now 0
        $this->assertSame(0, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));

        // Customer B attempts checkout immediately for the same item
        $responseB = $this->actingAs($customerB)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Customer B',
                'phone_number' => '09172222222',
                'address_line' => '102 Second Ave',
                'barangay' => 'Poblacion',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'payment_method_id' => $paymentMethodId,
            ]);

        $responseB->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors([
                'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
            ]);

        // Verifications
        $this->assertSame(0, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertDatabaseHas('orders', ['user_id' => $customerA->user_id]);
        $this->assertDatabaseMissing('orders', ['user_id' => $customerB->user_id]);
        $this->assertSame(1, DB::table('orders')->count());
    }

    /**
     * Requirement 4: Multiple customers purchasing a product with limited inventory.
     * Initial stock: 3. 5 customers attempt to purchase. Exactly 3 succeed, 2 fail.
     */
    public function test_multiple_customers_purchasing_product_with_limited_inventory(): void
    {
        $productId = $this->createProduct(stock: 3);
        $paymentMethodId = $this->createPaymentMethod();

        $successCount = 0;
        $failureCount = 0;

        for ($i = 1; $i <= 5; $i++) {
            $customer = User::factory()->create();
            $this->addCartItem($customer, $productId, quantity: 1);

            $response = $this->actingAs($customer)
                ->from(route('checkout.index'))
                ->post(route('checkout.store'), [
                    'recipient_name' => "Customer {$i}",
                    'phone_number' => "0917000000{$i}",
                    'address_line' => "Street {$i}",
                    'barangay' => 'Poblacion',
                    'city' => 'Bantayan',
                    'province' => 'Cebu',
                    'payment_method_id' => $paymentMethodId,
                ]);

            if ($response->isRedirect(route('account'))) {
                $successCount++;
            } else {
                $response->assertSessionHasErrors('cart');
                $failureCount++;
            }
        }

        $this->assertSame(3, $successCount, 'Exactly 3 customers should successfully purchase.');
        $this->assertSame(2, $failureCount, 'Exactly 2 customers should be rejected due to insufficient stock.');
        $this->assertSame(0, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertSame(3, DB::table('orders')->count());
    }

    /**
     * Requirement 5: Prevention of negative inventory.
     */
    public function test_prevention_of_negative_inventory_and_invalid_quantities(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 1);
        $paymentMethodId = $this->createPaymentMethod();

        // 1. Direct atomic check: conditional decrement with insufficient stock returns 0 affected rows
        $affected = DB::table('products')
            ->where('product_id', $productId)
            ->where('stock_quantity', '>=', 5)
            ->decrement('stock_quantity', 5);

        $this->assertSame(0, $affected, 'Deduction with insufficient stock must affect 0 rows.');
        $this->assertSame(1, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));

        // 2. Invalid/negative quantity in cart rejected by backend validation
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $user->user_id,
            'cart_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => -1,
            'unit_price' => 89,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ]);

        $response->assertSessionHasErrors('cart');
        $this->assertSame(1, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
    }

    /**
     * Requirement 6: Transaction rollback when order creation fails.
     * Stock must not be deducted if any downstream step inside the transaction fails.
     */
    public function test_transaction_rollback_when_order_creation_fails(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 2);

        // Invalid payment method id that passes preliminary request validation but is not in payment_methods table
        $invalidPaymentMethodId = 999999;

        $response = $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Mango Street',
                'barangay' => 'Poblacion',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'payment_method_id' => $invalidPaymentMethodId,
            ]);

        $response->assertRedirect(route('checkout.index'));

        // Stock must be completely rolled back to 5
        $this->assertSame(5, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseMissing('order_items', ['product_id' => $productId]);
    }

    /**
     * Requirement 7: Stock restoration after legitimate cancellation.
     */
    public function test_stock_restoration_after_legitimate_cancellation(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 10);
        $this->addCartItem($user, $productId, quantity: 4);
        $paymentMethodId = $this->createPaymentMethod();

        $this->actingAs($user)->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ]);

        $this->assertSame(6, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));

        $orderId = (int) DB::table('orders')->where('user_id', $user->user_id)->value('order_id');

        // Cancel order via OrderWorkflow
        $workflow = app(OrderWorkflow::class);
        $workflow->transition($orderId, 'cancelled', adminId: null, notes: 'Customer changed mind');

        // Stock must be restored to 10
        $this->assertSame(10, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertNotNull(DB::table('orders')->where('order_id', $orderId)->value('stock_restored_at'));
    }

    /**
     * Requirement 8: Prevention of duplicate stock restoration.
     */
    public function test_prevention_of_duplicate_stock_restoration(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 10);
        $this->addCartItem($user, $productId, quantity: 3);
        $paymentMethodId = $this->createPaymentMethod();

        $this->actingAs($user)->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ]);

        $this->assertSame(7, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $orderId = (int) DB::table('orders')->where('user_id', $user->user_id)->value('order_id');

        $workflow = app(OrderWorkflow::class);
        $workflow->transition($orderId, 'cancelled', adminId: null, notes: 'First cancellation');

        $this->assertSame(10, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));

        // Attempt second cancellation - transition table disallows cancelled -> cancelled
        try {
            $workflow->transition($orderId, 'cancelled', adminId: null, notes: 'Duplicate cancellation');
        } catch (\Throwable $e) {
            // Expected
        }

        // Stock must strictly remain 10, never duplicate to 13
        $this->assertSame(10, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
    }

    /**
     * Requirement 9: Repeated checkout requests and duplicate order submissions.
     */
    public function test_repeated_checkout_requests_when_stock_exhausted(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 1);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();

        $checkoutData = [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ];

        // First submission succeeds
        $this->actingAs($user)->post(route('checkout.store'), $checkoutData)
            ->assertRedirect(route('account'));

        $this->assertSame(0, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));

        // Repeated submission for the same product when stock is 0
        $this->addCartItem($user, $productId, quantity: 1);
        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $checkoutData)
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors([
                'cart' => 'Sorry! This product is no longer available in the requested quantity. Please update your cart.',
            ]);

        $this->assertSame(0, (int) DB::table('products')->where('product_id', $productId)->value('stock_quantity'));
        $this->assertSame(1, DB::table('orders')->count());
    }

    private function createProduct(int $stock): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ]);

        return (int) DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => 'Signature Milk Tea',
            'base_price' => 89,
            'is_available' => true,
            'stock_quantity' => $stock,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPaymentMethod(): int
    {
        return (int) DB::table('payment_methods')->insertGetId([
            'method_name' => 'Cash on Delivery',
            'is_active' => true,
        ]);
    }

    private function addCartItem(User $user, int $productId, int $quantity): void
    {
        $cart = DB::table('carts')
            ->where('user_id', $user->user_id)
            ->where('cart_status', 'active')
            ->first();

        $cartId = $cart !== null
            ? $cart->cart_id
            : DB::table('carts')->insertGetId([
                'user_id' => $user->user_id,
                'cart_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'size_id' => null,
            'quantity' => $quantity,
            'unit_price' => 89,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
