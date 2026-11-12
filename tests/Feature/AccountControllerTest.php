<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderConfirmationMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use CreatesAccountTables;
    use CreatesDeliveryFeeTable;
    use CreatesMenuTables;
    use CreatesOrderTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->createAccountTables();
        $this->createDeliveryFeeTable();
        $this->createMenuTables();
        $this->createOrderTables();
    }

    public function test_account_shows_only_the_authenticated_users_orders_and_addresses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->insertAddress($user->user_id, 'Home');
        $orderId = $this->insertOrder($user->user_id, 'SIP-1001', 'preparing');
        $this->insertOrderItem($orderId, 'Brown Sugar Milk Tea', 2, 178);
        $this->insertDelivery($orderId, 'TRACK-1001');
        $this->insertOrder($user->user_id, 'SIP-1002', 'completed');

        $otherOrderId = $this->insertOrder($otherUser->user_id, 'PRIVATE-ORDER', 'completed');
        $this->insertOrderItem($otherOrderId, 'Private drink', 1, 50);
        $this->insertAddress($otherUser->user_id, 'Private address');

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('My orders')
            ->assertSee('SIP-1001')
            ->assertSee('Delivered')
            ->assertSee('Brown Sugar Milk Tea')
            ->assertSee('TRACK-1001')
            ->assertSee('Home')
            ->assertDontSee('PRIVATE-ORDER')
            ->assertDontSee('Private drink')
            ->assertDontSee('Private address');
    }

    public function test_customer_can_cancel_only_their_pending_order_and_inventory_is_restored_once(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => 'Brown Sugar Milk Tea',
            'base_price' => 80,
            'stock_quantity' => 3,
            'is_available' => true,
        ]);
        $orderId = $this->insertOrder($user->user_id, 'SIP-CANCEL-1', 'pending');
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_name' => 'Brown Sugar Milk Tea',
            'quantity' => 2,
            'unit_price' => 80,
            'line_total' => 160,
        ]);

        $this->actingAs($otherUser)
            ->post(route('account.orders.cancel', $orderId))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('<dialog', false)
            ->assertSee('Cancellation reason')
            ->assertSee('Confirm cancellation');

        $this->post(route('account.orders.cancel', $orderId))
            ->assertSessionHasErrors('cancellation_reason');

        $this->post(route('account.orders.cancel', $orderId), [
            'cancellation_reason' => 'I ordered the wrong drink.',
        ])
            ->assertRedirect(route('account'))
            ->assertSessionHas('status', 'Your order has been cancelled.');

        $this->post(route('account.orders.cancel', $orderId), [
            'cancellation_reason' => 'I ordered the wrong drink.',
        ])
            ->assertSessionHasErrors('order_status');

        $this->assertDatabaseHas('orders', ['order_id' => $orderId, 'order_status' => 'cancelled']);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $orderId,
            'status' => 'cancelled',
            'notes' => 'I ordered the wrong drink.',
        ]);
        $this->assertDatabaseHas('products', ['product_id' => $productId, 'stock_quantity' => 5]);

        $this->get(route('account'))
            ->assertOk()
            ->assertSee('I ordered the wrong drink.');
    }

    public function test_customer_cancelling_an_order_sends_cancellation_email(): void
    {
        $user = User::factory()->create();
        $orderId = $this->insertOrder($user->user_id, 'SIP-EMAIL-CANCEL', 'pending');

        $this->mock(OrderConfirmationMailer::class, function ($mock) use ($orderId): void {
            $mock->shouldReceive('sendCancelled')->once()->with($orderId, 'Changed my mind');
        });

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $orderId), [
                'cancellation_reason' => 'Changed my mind',
            ])
            ->assertRedirect(route('account'))
            ->assertSessionHas('status', 'Your order has been cancelled.');
    }

    public function test_customer_cannot_cancel_order_that_is_out_for_delivery(): void
    {
        $user = User::factory()->create();
        $orderId = $this->insertOrder($user->user_id, 'SIP-OUT-DELIVERY', 'out_for_delivery');

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertDontSee('Cancel order');

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $orderId), [
                'cancellation_reason' => 'Too late to cancel',
            ])
            ->assertSessionHasErrors('order_status');

        $this->assertDatabaseHas('orders', [
            'order_id' => $orderId,
            'order_status' => 'out_for_delivery',
        ]);
    }

    public function test_customer_can_delete_their_order_from_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $orderId = $this->insertOrder($user->user_id, 'SIP-DEL-1', 'completed');
        $this->insertOrderItem($orderId, 'Iced Latte', 1, 120);

        // Another user cannot delete it
        $this->actingAs($otherUser)
            ->delete(route('account.orders.destroy', $orderId))
            ->assertNotFound();

        $this->assertDatabaseHas('orders', ['order_id' => $orderId]);

        // Owner can delete it
        $this->actingAs($user)
            ->delete(route('account.orders.destroy', $orderId))
            ->assertRedirect(route('account'))
            ->assertSessionHas('status', 'Your order has been deleted from your history.');

        $this->assertDatabaseMissing('orders', ['order_id' => $orderId]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $orderId]);
    }

    public function test_customer_cannot_delete_order_that_is_out_for_delivery(): void
    {
        $user = User::factory()->create();
        $orderId = $this->insertOrder($user->user_id, 'SIP-DEL-OUT', 'out_for_delivery');

        $this->actingAs($user)
            ->delete(route('account.orders.destroy', $orderId))
            ->assertSessionHasErrors('order_status');

        $this->assertDatabaseHas('orders', [
            'order_id' => $orderId,
            'order_status' => 'out_for_delivery',
        ]);
    }

    public function test_customer_can_clear_their_order_history(): void
    {
        $user = User::factory()->create();
        $completedOrderId = $this->insertOrder($user->user_id, 'SIP-CLR-1', 'completed');
        $cancelledOrderId = $this->insertOrder($user->user_id, 'SIP-CLR-2', 'cancelled');
        $activeOrderId = $this->insertOrder($user->user_id, 'SIP-CLR-3', 'out_for_delivery');

        $this->actingAs($user)
            ->delete(route('account.orders.clear'))
            ->assertRedirect(route('account'))
            ->assertSessionHas('status', 'Your order history has been cleared.');

        $this->assertDatabaseMissing('orders', ['order_id' => $completedOrderId]);
        $this->assertDatabaseMissing('orders', ['order_id' => $cancelledOrderId]);
        // Out for delivery order remains untouched
        $this->assertDatabaseHas('orders', ['order_id' => $activeOrderId]);
    }

    public function test_profile_update_requires_the_current_password_and_ignores_unapproved_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'current-password',
        ]);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'first_name' => 'Jamie',
                'last_name' => 'Rivera',
                'email' => ' NEW@example.com ',
                'phone_number' => '09171234567',
                'current_password' => 'current-password',
                'is_active' => false,
            ])
            ->assertRedirect(route('account'));

        $user->refresh();
        $this->assertSame('Jamie', $user->first_name);
        $this->assertSame('Rivera', $user->last_name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('09171234567', $user->phone_number);
        $this->assertTrue($user->is_active);

        $this->from(route('account'))
            ->put(route('account.profile.update'), [
                'first_name' => 'Changed',
                'last_name' => 'Name',
                'email' => 'new@example.com',
                'current_password' => 'incorrect-password',
            ])
            ->assertRedirect(route('account'))
            ->assertSessionHasErrors('current_password');

        $this->assertSame('Jamie', $user->fresh()->first_name);
    }

    public function test_password_can_be_changed_with_the_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'current-password',
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'current-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('account'));

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_profile_photo_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('account.photo.store'), [
            'photo' => $this->fakePng('first.png'),
        ])->assertRedirect(route('account'));

        $firstPhotoPath = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($firstPhotoPath);

        $this->get(route('account'))
            ->assertOk()
            ->assertSee('src="'.url('/storage/'.$firstPhotoPath).'"', false);

        $this->post(route('account.photo.store'), [
            'photo' => $this->fakePng('second.png'),
        ])->assertRedirect(route('account'));

        $secondPhotoPath = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertMissing($firstPhotoPath);
        Storage::disk('public')->assertExists($secondPhotoPath);

        $this->delete(route('account.photo.destroy'))
            ->assertRedirect(route('account'));

        $this->assertNull($user->fresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($secondPhotoPath);
    }

    public function test_only_valid_images_can_be_uploaded_as_profile_photos(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->from(route('account'))
            ->post(route('account.photo.store'), [
                'photo' => UploadedFile::fake()->create('avatar.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('account'))
            ->assertSessionHasErrors('photo');
    }

    public function test_addresses_can_be_created_updated_defaulted_and_removed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('account.addresses.store'), $this->addressData([
            'label' => 'Home',
        ]))->assertRedirect(route('account'));

        $homeAddressId = (int) DB::table('user_addresses')->where('label', 'Home')->value('address_id');
        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $homeAddressId,
            'user_id' => $user->user_id,
            'is_default' => true,
        ]);

        $this->post(route('account.addresses.store'), $this->addressData([
            'label' => 'Work',
            'city' => 'Madridejos',
            'is_default' => '1',
        ]))->assertRedirect(route('account'));

        $workAddressId = (int) DB::table('user_addresses')->where('label', 'Work')->value('address_id');
        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $homeAddressId,
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $workAddressId,
            'is_default' => true,
        ]);

        $this->put(route('account.addresses.update', $workAddressId), $this->addressData([
            'label' => 'Office',
            'city' => 'Sta. Fe',
            'is_default' => '1',
        ]))->assertRedirect(route('account'));

        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $workAddressId,
            'label' => 'Office',
            'city' => 'Sta. Fe',
            'is_default' => true,
        ]);

        $this->patch(route('account.addresses.default', $homeAddressId))
            ->assertRedirect(route('account'));

        $this->put(route('account.addresses.update', $homeAddressId), $this->addressData([
            'label' => 'Updated home',
        ]))->assertRedirect(route('account'));

        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $workAddressId,
            'is_default' => true,
        ]);

        $this->patch(route('account.addresses.default', $homeAddressId))
            ->assertRedirect(route('account'));

        $this->delete(route('account.addresses.destroy', $homeAddressId))
            ->assertRedirect(route('account'));

        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $workAddressId,
            'is_default' => true,
        ]);
        $this->assertDatabaseMissing('user_addresses', ['address_id' => $homeAddressId]);
    }

    public function test_users_cannot_read_or_change_another_users_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $addressId = $this->insertAddress($otherUser->user_id, 'Private home');

        $this->actingAs($user)
            ->put(route('account.addresses.update', $addressId), $this->addressData())
            ->assertNotFound();

        $this->assertDatabaseHas('user_addresses', [
            'address_id' => $addressId,
            'user_id' => $otherUser->user_id,
            'label' => 'Private home',
        ]);
    }

    public function test_address_cannot_be_saved_for_a_municipality_without_a_delivery_fee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('account'))
            ->post(route('account.addresses.store'), $this->addressData([
                'city' => 'Cebu City',
            ]))
            ->assertRedirect(route('account'))
            ->assertSessionHasErrors('city');

        $this->assertDatabaseMissing('user_addresses', ['user_id' => $user->user_id]);
    }

    public function test_account_address_form_accepts_a_municipality_added_by_admin(): void
    {
        DB::table('delivery_fees')->insert([
            'municipality' => 'Daanbantayan',
            'fee' => 450,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Daanbantayan');

        $this->post(route('account.addresses.store'), $this->addressData([
            'city' => 'Daanbantayan',
        ]))->assertRedirect(route('account'));

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->user_id,
            'city' => 'Daanbantayan',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function addressData(array $overrides = []): array
    {
        return [
            'label' => 'Home',
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Sample Street',
            'barangay' => 'Barangay One',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'postal_code' => '1000',
            ...$overrides,
        ];
    }

    private function insertAddress(int $userId, string $label): int
    {
        return (int) DB::table('user_addresses')->insertGetId($this->addressData([
            'user_id' => $userId,
            'label' => $label,
            'recipient_name' => 'Account owner',
            'phone_number' => '09170000000',
            'address_line' => '1 Private Street',
            'barangay' => 'Private Barangay',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function insertOrder(int $userId, string $orderNumber, string $status): int
    {
        return (int) DB::table('orders')->insertGetId([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'order_status' => $status,
            'subtotal' => 178,
            'delivery_fee' => 0,
            'discount_amount' => 0,
            'grand_total' => 178,
            'recipient_name' => 'Account owner',
            'recipient_phone' => '09170000000',
            'delivery_address' => '1 Sample Street, Manila',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertOrderItem(int $orderId, string $productName, int $quantity, float $lineTotal): void
    {
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_name' => $productName,
            'quantity' => $quantity,
            'unit_price' => $lineTotal / $quantity,
            'line_total' => $lineTotal,
        ]);
    }

    private function insertDelivery(int $orderId, string $trackingReference): void
    {
        DB::table('deliveries')->insert([
            'order_id' => $orderId,
            'tracking_reference' => $trackingReference,
        ]);
    }

    private function fakePng(string $filename): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $filename,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf5kAAAAASUVORK5CYII='),
        );
    }
}
