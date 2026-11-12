<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesAdminTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\TestCase;

class DeliveryFeeControllerTest extends TestCase
{
    use CreatesAdminTables;
    use CreatesDeliveryFeeTable;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminTables();
        $this->createDeliveryFeeTable();
        $this->withoutVite();
    }

    public function test_admin_can_view_and_update_each_municipality_delivery_fee(): void
    {
        $admin = $this->createAdmin();
        $feeIds = DB::table('delivery_fees')->pluck('delivery_fee_id', 'municipality');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.delivery-fees.index'))
            ->assertSee('Add municipality')
            ->assertOk()
            ->assertSee('Bantayan delivery fee')
            ->assertSee('Madridejos delivery fee')
            ->assertSee('Sta. Fe delivery fee');

        $this->put(route('admin.delivery-fees.update'), [
            'fees' => [
                $feeIds['Bantayan'] => '125.00',
                $feeIds['Madridejos'] => '325.50',
                $feeIds['Sta. Fe'] => '225.00',
            ],
        ])->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHas('status', 'Delivery fees updated.');

        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Bantayan', 'fee' => '125.00']);
        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Madridejos', 'fee' => '325.50']);
        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Sta. Fe', 'fee' => '225.00']);
    }

    public function test_guests_cannot_change_delivery_fees(): void
    {
        $this->post(route('admin.delivery-fees.store'), [
            'municipality' => 'New Place',
            'fee' => '1',
        ])->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('delivery_fees', ['municipality' => 'New Place']);
        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Bantayan', 'fee' => '100.00']);
    }

    public function test_admin_can_add_a_municipality_with_its_delivery_fee(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.delivery-fees.store'), [
                'municipality' => 'Daanbantayan',
                'fee' => '450.00',
            ])
            ->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHas('status', 'Municipality added.');

        $this->assertDatabaseHas('delivery_fees', [
            'municipality' => 'Daanbantayan',
            'fee' => '450.00',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.delivery-fees.index'))
            ->assertOk()
            ->assertSee('Daanbantayan delivery fee');
    }

    public function test_admin_cannot_add_a_duplicate_municipality_or_negative_fee(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.delivery-fees.index'))
            ->post(route('admin.delivery-fees.store'), [
                'municipality' => 'Bantayan',
                'fee' => '-1',
            ])
            ->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHasErrors(['municipality', 'fee']);

        $this->assertDatabaseCount('delivery_fees', 3);
    }

    public function test_invalid_fee_does_not_change_any_municipality_fee(): void
    {
        $admin = $this->createAdmin();
        $feeIds = DB::table('delivery_fees')->pluck('delivery_fee_id', 'municipality');

        $this->actingAs($admin, 'admin')
            ->from(route('admin.delivery-fees.index'))
            ->put(route('admin.delivery-fees.update'), [
                'fees' => [
                    $feeIds['Bantayan'] => '125.00',
                    $feeIds['Madridejos'] => '-1',
                    $feeIds['Sta. Fe'] => '225.00',
                ],
            ])
            ->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHasErrors('fees.'.$feeIds['Madridejos']);

        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Bantayan', 'fee' => '100.00']);
        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Madridejos', 'fee' => '300.00']);
    }

    public function test_missing_delivery_fee_value_is_rejected_without_changing_fees(): void
    {
        $admin = $this->createAdmin();
        $feeIds = DB::table('delivery_fees')->pluck('delivery_fee_id', 'municipality');

        $this->actingAs($admin, 'admin')
            ->from(route('admin.delivery-fees.index'))
            ->put(route('admin.delivery-fees.update'), [
                'fees' => [
                    $feeIds['Bantayan'] => '125.00',
                    $feeIds['Madridejos'] => '325.00',
                ],
            ])
            ->assertRedirect(route('admin.delivery-fees.index'))
            ->assertSessionHasErrors('fees.'.$feeIds['Sta. Fe']);

        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Bantayan', 'fee' => '100.00']);
        $this->assertDatabaseHas('delivery_fees', ['municipality' => 'Madridejos', 'fee' => '300.00']);
    }

    private function createAdmin(): Admin
    {
        return Admin::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'delivery-admin@example.com',
            'password' => 'admin-password',
            'role' => 'owner',
            'is_active' => true,
        ]);
    }
}
