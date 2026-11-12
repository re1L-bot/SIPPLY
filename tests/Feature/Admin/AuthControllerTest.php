<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAdminTables;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use CreatesAdminTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminTables();
        $this->withoutVite();
    }

    public function test_admin_sign_in_uses_the_admin_table_and_updates_last_login(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'admin@example.com',
            'password' => 'admin-password',
            'is_active' => true,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => ' ADMIN@example.com ',
            'password' => 'admin-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotNull($admin->fresh()->last_login_at);
        $this->assertGuest('web');
    }

    public function test_customer_accounts_cannot_sign_in_as_administrators(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'customer-password',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'customer@example.com',
                'password' => 'customer-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
        $this->assertGuest('web');
    }

    public function test_inactive_admins_cannot_sign_in(): void
    {
        Admin::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'inactive-admin@example.com',
            'password' => 'admin-password',
            'is_active' => false,
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'inactive-admin@example.com',
                'password' => 'admin-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_admin_pages_redirect_guests_to_the_admin_sign_in_page(): void
    {
        $this->get(route('admin.menu.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_can_open_the_administrator_sign_in_page(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Administrator sign in')
            ->assertSee('Admin accounts are created separately');
    }

    public function test_admin_logout_ends_the_admin_session(): void
    {
        $admin = Admin::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'admin@example.com',
            'password' => 'admin-password',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }
}
