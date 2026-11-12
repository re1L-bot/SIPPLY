<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderConfirmationMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
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
    }

    public function test_checkout_places_order_sends_confirmation_and_reduces_stock(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 2);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('send')
                ->once()
                ->withArgs(function (int $orderId, string $recipientEmail) use ($user): bool {
                    return $orderId > 0 && $recipientEmail === $user->email;
                });
        });

        $this->actingAs($user)
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'postal_code' => '1000',
                'save_address' => '1',
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('account'))
            ->assertSessionHas('status');

        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('100.00', number_format((float) $order->delivery_fee, 2, '.', ''));
        $this->assertSame('278.00', number_format((float) $order->grand_total, 2, '.', ''));
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->order_id,
            'product_id' => $productId,
            'quantity' => 2,
            'unit_price' => '89.00',
            'line_total' => '178.00',
        ]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 3,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'payment_method_id' => $paymentMethodId,
            'amount' => '278.00',
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->order_id,
            'delivery_status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->order_id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'recipient_name' => 'Jamie Rivera',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->user_id,
            'cart_status' => 'converted',
        ]);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $productId]);
    }

    public function test_checkout_uses_the_selected_size_price_and_saves_its_name_on_the_order(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $largeSizeId = (int) DB::table('product_sizes')->where('size_name', 'Large')->value('size_id');
        $this->addCartItem($user, $productId, quantity: 2, sizeId: $largeSizeId);
        $paymentMethodId = $this->createPaymentMethod();
        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->actingAs($user)
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('account'));

        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertSame('318.00', number_format((float) $order->grand_total, 2, '.', ''));
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->order_id,
            'product_id' => $productId,
            'size_name' => 'Large',
            'quantity' => 2,
            'unit_price' => '109.00',
            'line_total' => '218.00',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'amount' => '318.00',
        ]);
    }

    public function test_guest_can_place_an_order_and_receives_the_configured_municipality_fee(): void
    {
        $productId = $this->createProduct(stock: 5);
        $largeSizeId = (int) DB::table('product_sizes')->where('size_name', 'Large')->value('size_id');
        $paymentMethodId = $this->createPaymentMethod();
        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once()->withArgs(
                fn (int $orderId, string $email): bool => $orderId > 0 && $email === 'guest@example.test',
            );
        });

        $this->get(route('home'))->assertOk()->assertSee('Create account');
        $this->post(route('cart.items.store'), [
            'product_id' => $productId,
            'size_id' => $largeSizeId,
            'quantity' => 3,
        ])->assertRedirect(route('cart.index'));
        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="recipient_name"', false);

        $this->post(route('checkout.store'), [
            'email' => 'guest@example.test',
            'recipient_name' => 'Guest Customer',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ])->assertRedirect(route('home'))
            ->assertSessionHas('status')
            ->assertSessionMissing('guest_cart');

        $order = DB::table('orders')->where('customer_email', 'guest@example.test')->first();
        $this->assertNotNull($order);
        $this->assertNull($order->user_id);
        $this->assertSame('427.00', number_format((float) $order->grand_total, 2, '.', ''));
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->order_id,
            'product_id' => $productId,
            'size_name' => 'Large',
            'quantity' => 3,
            'unit_price' => '109.00',
            'line_total' => '327.00',
        ]);
        $this->assertDatabaseHas('products', ['product_id' => $productId, 'stock_quantity' => 2]);
    }

    public function test_registered_customer_with_saved_address_does_not_see_manual_address_or_email_inputs(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $this->createPaymentMethod();
        $this->createAddress($user);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="recipient_name"', false)
            ->assertDontSee('name="address_line"', false);
    }

    public function test_checkout_rejects_an_order_that_exceeds_stock_without_creating_records(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 1);
        $this->addCartItem($user, $productId, quantity: 2);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('send');
        });

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'save_address' => '1',
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 1,
        ]);
        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseMissing('user_addresses', ['user_id' => $user->user_id]);
    }

    public function test_customer_cannot_place_an_order_using_another_users_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();
        $addressId = $this->createAddress($otherUser);

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('send');
        });

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'address_id' => $addressId,
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('address_id');

        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 5,
        ]);
    }

    public function test_saved_address_hides_manual_address_fields_and_uses_its_municipality_fee(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();
        $addressId = $this->createAddress($user, municipality: 'Madridejos');

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Choose a saved delivery address')
            ->assertSee('Madridejos')
            ->assertDontSee('name="recipient_name"', false)
            ->assertDontSee('name="address_line"', false)
            ->assertDontSee('name="barangay"', false)
            ->assertDontSee('name="city"', false);

        $this->post(route('checkout.store'), [
            'address_id' => $addressId,
            'payment_method_id' => $paymentMethodId,
        ])->assertRedirect(route('account'));

        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertSame('300.00', number_format((float) $order->delivery_fee, 2, '.', ''));
        $this->assertSame('389.00', number_format((float) $order->grand_total, 2, '.', ''));
    }

    public function test_checkout_shows_the_supported_municipalities_and_delivery_fees_for_new_addresses(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $this->createPaymentMethod();

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('name="city"', false)
            ->assertSee('value="Bantayan" data-fee="100.00"', false)
            ->assertSee('value="Madridejos" data-fee="300.00"', false)
            ->assertSee('value="Sta. Fe" data-fee="200.00"', false);
    }

    public function test_checkout_accepts_an_admin_added_municipality_and_applies_its_fee(): void
    {
        DB::table('delivery_fees')->insert([
            'municipality' => 'Daanbantayan',
            'fee' => 450,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('value="Daanbantayan" data-fee="450.00"', false);

        $this->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => 'Daanbantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ])->assertRedirect(route('account'));

        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertSame('450.00', number_format((float) $order->delivery_fee, 2, '.', ''));
        $this->assertSame('539.00', number_format((float) $order->grand_total, 2, '.', ''));
    }

    public function test_checkout_rejects_an_address_outside_the_service_area(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('send');
        });

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Cebu City',
                'province' => 'Cebu',
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('city');

        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', ['product_id' => $productId, 'stock_quantity' => 5]);
    }

    public function test_checkout_requires_a_saved_address_when_the_customer_has_one(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $this->createPaymentMethod();
        $this->createAddress($user);

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Bantayan',
                'province' => 'Cebu',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('address_id');

        $this->assertDatabaseMissing('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', ['product_id' => $productId, 'stock_quantity' => 5]);
    }

    public function test_buy_now_checkout_uses_the_selected_quantity_without_converting_the_existing_cart(): void
    {
        $user = User::factory()->create();
        $buyNowProductId = $this->createProduct(stock: 5);
        $cartProductId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $cartProductId, quantity: 3);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->actingAs($user)
            ->post(route('buy-now', $buyNowProductId), ['quantity' => 3])
            ->assertRedirect(route('checkout.index'));

        $this->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'save_address' => '1',
            'payment_method_id' => $paymentMethodId,
        ])
            ->assertRedirect(route('account'));

        $this->assertDatabaseHas('order_items', [
            'product_id' => $buyNowProductId,
            'quantity' => 3,
        ]);
        $this->assertDatabaseMissing('order_items', ['product_id' => $cartProductId]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $cartProductId,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('products', [
            'product_id' => $buyNowProductId,
            'stock_quantity' => 2,
        ]);
        $this->assertDatabaseHas('products', [
            'product_id' => $cartProductId,
            'stock_quantity' => 5,
        ]);
    }

    public function test_checkout_reports_email_failure_without_rolling_back_the_order(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 2);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();

        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new RuntimeException('SMTP unavailable'));
        });

        $this->actingAs($user)
            ->post(route('checkout.store'), [
                'recipient_name' => 'Jamie Rivera',
                'phone_number' => '09171234567',
                'address_line' => '123 Sample Street',
                'barangay' => 'Barangay One',
                'city' => 'Bantayan',
                'province' => 'Cebu',
                'payment_method_id' => $paymentMethodId,
            ])
            ->assertRedirect(route('account'))
            ->assertSessionHas('email_warning');

        $this->assertDatabaseHas('orders', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 1,
        ]);
        $this->assertDatabaseMissing('user_addresses', [
            'user_id' => $user->user_id,
        ]);
    }

    public function test_confirmation_email_escapes_customer_and_product_content(): void
    {
        $content = view('emails.orders.confirmation', [
            'order' => (object) [
                'order_number' => 'SP-TEST123',
                'recipient_name' => '<script>alert(1)</script>',
                'delivery_address' => '1 Safe Street',
                'delivery_fee' => '100.00',
                'grand_total' => '189.00',
                'method_name' => 'Cash on Delivery',
            ],
            'items' => collect([
                (object) [
                    'quantity' => 1,
                    'product_name' => '<img src=x onerror=alert(1)>',
                    'unit_price' => '89.00',
                    'line_total' => '89.00',
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('&lt;script&gt;', $content);
        $this->assertStringContainsString('&lt;img', $content);
        $this->assertStringContainsString('₱100.00', $content);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $content);
    }

    public function test_valid_coupon_is_applied_to_the_order_and_usage_counted_once(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $this->addCartItem($user, $productId, quantity: 1);
        $paymentMethodId = $this->createPaymentMethod();
        DB::table('discount_coupons')->insert([
            'code' => 'HALFOFF',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'usage_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->mock(OrderConfirmationMailer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once();
        });

        $this->actingAs($user)
            ->post(route('checkout.coupon.apply'), ['coupon_code' => 'halfoff'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('status', 'Coupon applied.');

        $this->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
        ])->assertRedirect(route('account'));

        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertSame('HALFOFF', $order->coupon_code);
        $this->assertSame('44.50', number_format((float) $order->discount_amount, 2, '.', ''));
        $this->assertSame('144.50', number_format((float) $order->grand_total, 2, '.', ''));
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->order_id,
            'amount' => '144.50',
        ]);
        $this->assertDatabaseHas('discount_coupons', [
            'code' => 'HALFOFF',
            'used_count' => 1,
        ]);
    }

    private function createProduct(int $stock): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ]);

        $productId = (int) DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => fake()->unique()->words(2, true),
            'base_price' => 89,
            'is_available' => true,
            'stock_quantity' => $stock,
        ]);

        return $productId;
    }

    private function createPaymentMethod(): int
    {
        return (int) DB::table('payment_methods')->insertGetId([
            'method_name' => 'Cash on Delivery',
            'is_active' => true,
        ]);
    }

    private function createAddress(User $user, string $municipality = 'Bantayan'): int
    {
        return (int) DB::table('user_addresses')->insertGetId([
            'user_id' => $user->user_id,
            'label' => 'Home',
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => $municipality,
            'province' => 'Cebu',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function addCartItem(User $user, int $productId, int $quantity, ?int $sizeId = null): void
    {
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $user->user_id,
            'cart_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'size_id' => $sizeId,
            'quantity' => $quantity,
            'unit_price' => $sizeId === null
                ? 89
                : 89 + (float) DB::table('product_sizes')->where('size_id', $sizeId)->value('price_adjustment'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
