<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use App\Services\OrderConfirmationMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesAdminTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use CreatesAccountTables;
    use CreatesAdminTables;
    use CreatesDeliveryFeeTable;
    use CreatesMenuTables;
    use CreatesOrderTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAccountTables();
        $this->createAdminTables();
        $this->createDeliveryFeeTable();
        $this->createMenuTables();
        $this->createOrderTables();
        $this->withoutVite();
    }

    public function test_owner_can_update_order_status_and_the_action_is_logged(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $customer = User::factory()->create();
        $paymentMethodId = DB::table('payment_methods')->insertGetId([
            'method_name' => 'Cash on Delivery',
            'is_active' => true,
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customer->user_id,
            'order_number' => 'SP-TEST-ORDER',
            'order_status' => 'pending',
            'subtotal' => 100,
            'delivery_fee' => 10,
            'discount_amount' => 0,
            'grand_total' => 110,
            'recipient_name' => 'Jamie Rivera',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_name' => 'Brown Sugar Milk Tea',
            'quantity' => 2,
            'unit_price' => 50,
            'line_total' => 100,
        ]);
        DB::table('payments')->insert([
            'order_id' => $orderId,
            'payment_method_id' => $paymentMethodId,
            'amount' => 110,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('deliveries')->insert([
            'order_id' => $orderId,
            'delivery_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $orderId), [
                'order_status' => 'confirmed',
                'notes' => 'Payment and order details checked.',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status', 'Order status updated.');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'confirmed']);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $orderId,
            'admin_id' => $admin->admin_id,
            'status' => 'confirmed',
            'notes' => 'Payment and order details checked.',
        ]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_id' => $admin->admin_id,
            'entity_type' => 'order',
            'entity_id' => $orderId,
        ]);

        $this->get(route('admin.orders.show', $orderId))
            ->assertOk()
            ->assertSee('Brown Sugar Milk Tea')
            ->assertSee('Cash on Delivery')
            ->assertSee($customer->email);
    }

    public function test_order_cancellation_is_limited_to_eligible_statuses_and_restores_stock_once(): void
    {
        $customer = User::factory()->create();
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => 'Brown Sugar Tea',
            'base_price' => 80,
            'stock_quantity' => 3,
            'is_available' => true,
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customer->user_id,
            'customer_email' => 'customer@example.test',
            'order_number' => 'SP-CANCEL-TEST',
            'order_status' => 'pending',
            'subtotal' => 160,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'grand_total' => 160,
            'recipient_name' => 'Jamie Rivera',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_name' => 'Brown Sugar Tea',
            'quantity' => 2,
            'unit_price' => 80,
            'line_total' => 160,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $orderId))
            ->assertOk()
            ->assertSee('<dialog', false)
            ->assertSee('Confirm cancellation');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $orderId), ['order_status' => 'completed'])
            ->assertSessionHasErrors('order_status');

        $this->put(route('admin.orders.update', $orderId), ['order_status' => 'cancelled'])
            ->assertSessionHasErrors('cancellation_reason');
        $this->put(route('admin.orders.update', $orderId), [
            'order_status' => 'cancelled',
            'cancellation_reason' => 'Customer requested cancellation.',
        ])
            ->assertRedirect(route('admin.orders.index'));
        $this->put(route('admin.orders.update', $orderId), [
            'order_status' => 'cancelled',
            'cancellation_reason' => 'Customer requested cancellation.',
        ])
            ->assertSessionHasErrors('order_status');

        $this->assertDatabaseHas('products', ['product_id' => $productId, 'stock_quantity' => 5]);
        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'cancelled']);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $orderId,
            'status' => 'cancelled',
            'notes' => 'Customer requested cancellation.',
        ]);

        $this->actingAs($customer)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Customer requested cancellation.');
    }

    public function test_admin_marking_an_order_delivered_updates_delivery_and_emails_the_customer(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'customer_email' => 'customer@example.test',
            'order_number' => 'SP-DELIVERED-TEST',
            'order_status' => 'out_for_delivery',
            'subtotal' => 100,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'grand_total' => 100,
            'recipient_name' => 'Jamie Rivera',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('deliveries')->insert([
            'order_id' => $orderId,
            'delivery_status' => 'assigned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $deliveredEmail = view('emails.orders.delivered', [
            'order' => (object) [
                'order_number' => 'SP-DELIVERED-TEST',
                'recipient_name' => 'Jamie Rivera',
                'delivery_address' => 'Bantayan, Cebu',
            ],
        ])->render();
        $this->assertStringContainsString('Your order has been delivered!', $deliveredEmail);
        $this->assertStringContainsString('SP-DELIVERED-TEST', $deliveredEmail);

        $this->mock(OrderConfirmationMailer::class, function ($mock) use ($orderId): void {
            $mock->shouldReceive('sendDelivered')->once()->with($orderId);
        });

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $orderId))
            ->assertOk()
            ->assertSee('Out for delivery')
            ->assertSee('Delivered');

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $orderId), ['order_status' => 'completed'])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status', 'Order marked as delivered and customer notified by email.');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'completed']);
        $this->assertDatabaseHas('deliveries', ['order_id' => $orderId, 'delivery_status' => 'delivered']);
    }

    public function test_admin_cancelling_an_order_emails_the_customer_and_displays_reason(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'customer_email' => 'customer@example.test',
            'order_number' => 'SP-CANCEL-EMAIL-TEST',
            'order_status' => 'out_for_delivery',
            'subtotal' => 120,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'grand_total' => 120,
            'recipient_name' => 'Jamie Rivera',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('deliveries')->insert([
            'order_id' => $orderId,
            'delivery_status' => 'assigned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cancelledEmail = view('emails.orders.cancelled', [
            'order' => (object) [
                'order_number' => 'SP-CANCEL-EMAIL-TEST',
                'recipient_name' => 'Jamie Rivera',
                'delivery_address' => 'Bantayan, Cebu',
            ],
            'cancellationReason' => 'Delivery location could not be reached.',
        ])->render();
        $this->assertStringContainsString('Your order has been cancelled', $cancelledEmail);
        $this->assertStringContainsString('SP-CANCEL-EMAIL-TEST', $cancelledEmail);
        $this->assertStringContainsString('Delivery location could not be reached.', $cancelledEmail);

        $this->mock(OrderConfirmationMailer::class, function ($mock) use ($orderId): void {
            $mock->shouldReceive('sendCancelled')->once()->with($orderId, 'Delivery location could not be reached.');
        });

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $orderId), [
                'order_status' => 'cancelled',
                'cancellation_reason' => 'Delivery location could not be reached.',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status', 'Order cancelled and customer notified by email.');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'cancelled']);
        $this->assertDatabaseHas('deliveries', ['order_id' => $orderId, 'delivery_status' => 'failed']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $orderId))
            ->assertOk()
            ->assertSee('Order Cancelled')
            ->assertSee('Delivery location could not be reached.');

        $this->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Cancellation reason:')
            ->assertSee('Delivery location could not be reached.');
    }

    public function test_orders_index_filter_only_displays_delivered_preparing_out_of_delivery_cancel_order(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Delivered')
            ->assertSee('Preparing')
            ->assertSee('Out of delivery')
            ->assertSee('Cancel order')
            ->assertDontSee('<option value="pending"', false)
            ->assertDontSee('<option value="confirmed"', false)
            ->assertDontSee('<option value="ready"', false);
    }

    public function test_admin_can_update_order_from_pending_to_preparing_and_status_form_has_no_blocking_required_on_dialog(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'SP-STATUS-TEST',
            'order_status' => 'pending',
            'subtotal' => 100,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'grand_total' => 100,
            'recipient_name' => 'Alex Reyes',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.orders.index'));
        $response->assertOk();
        // Check that textarea inside dialog does not have the HTML `required` attribute that breaks form submission
        $this->assertStringNotContainsString('data-cancellation-reason required', $response->getContent());
        $this->assertStringNotContainsString('required data-cancellation-reason', $response->getContent());

        // Update pending to preparing
        $this->put(route('admin.orders.update', $orderId), [
            'order_status' => 'preparing',
            'notes' => 'Brewing espresso.',
        ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status', 'Order status updated.');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'preparing']);

        // Update preparing to out_for_delivery
        $this->put(route('admin.orders.update', $orderId), [
            'order_status' => 'out_for_delivery',
            'notes' => 'Handed over to rider.',
        ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status', 'Order status updated.');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'out_for_delivery']);
    }

    public function test_admin_customer_summary_groups_order_emails_and_uses_latest_order_details(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $placedAt = now()->subDay();
        foreach ([
            ['customer@example.test', 'Jamie Rivera', '09170000001', 'pending', 100, $placedAt],
            ['CUSTOMER@example.test', 'Jamie Updated', '09170000002', 'completed', 150, now()],
        ] as [$email, $name, $phone, $status, $total, $date]) {
            DB::table('orders')->insert([
                'customer_email' => $email,
                'order_number' => 'SP-'.strtoupper(fake()->unique()->lexify('??????')),
                'order_status' => $status,
                'subtotal' => $total,
                'delivery_fee' => 0,
                'discount_amount' => 0,
                'grand_total' => $total,
                'recipient_name' => $name,
                'recipient_phone' => $phone,
                'delivery_address' => 'Bantayan, Cebu',
                'placed_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('customer@example.test')
            ->assertSee('Jamie Updated')
            ->assertSee('09170000002')
            ->assertSee('₱150.00')
            ->assertDontSee('Deactivate');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Completed Orders')
            ->assertSee('Total Sales')
            ->assertSee('₱150.00');
    }

    public function test_deactivated_admin_is_signed_out_of_existing_sessions(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Staff',
            'email' => 'staff@example.test',
            'password' => 'a-strong-password',
            'role' => 'staff',
            'is_active' => true,
        ]);
        DB::table('admins')->where('admin_id', $admin->admin_id)->update(['is_active' => false]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_owner_can_open_each_admin_module_page(): void
    {
        $owner = Admin::query()->create([
            'first_name' => 'Store',
            'last_name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->actingAs($owner, 'admin');

        foreach ([
            'admin.dashboard',
            'admin.menu.index',
            'admin.orders.index',
            'admin.inventory.index',
            'admin.customers.index',
            'admin.delivery-fees.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }
}
