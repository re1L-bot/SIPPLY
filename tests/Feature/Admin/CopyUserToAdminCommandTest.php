<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesAdminTables;
use Tests\TestCase;

class CopyUserToAdminCommandTest extends TestCase
{
    use CreatesAdminTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminTables();
    }

    public function test_command_copies_an_active_customer_into_admins_without_changing_the_customer(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'member@example.com',
            'password' => 'customer-password',
            'phone_number' => '09171234567',
        ]);
        $passwordHash = $user->password;

        $this->artisan('admin:copy-user-to-admin', ['email' => ' MEMBER@example.com '])
            ->expectsOutputToContain('The administrator can sign in with the same password.')
            ->assertSuccessful();

        $this->assertDatabaseHas('admins', [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => 'member@example.com',
            'password' => $passwordHash,
            'phone_number' => '09171234567',
            'is_active' => true,
            'last_login_at' => null,
        ]);
        $this->assertSame($passwordHash, $user->fresh()->password);
    }

    public function test_command_refuses_to_copy_inactive_customers(): void
    {
        User::factory()->inactive()->create(['email' => 'inactive@example.com']);

        $this->artisan('admin:copy-user-to-admin', ['email' => 'inactive@example.com'])
            ->expectsOutputToContain('An inactive customer account cannot be copied to administrators.')
            ->assertFailed();

        $this->assertDatabaseMissing('admins', ['email' => 'inactive@example.com']);
    }

    public function test_command_refuses_duplicate_administrator_emails(): void
    {
        User::factory()->create(['email' => 'member@example.com']);
        DB::table('admins')->insert([
            'first_name' => 'Existing',
            'last_name' => 'Admin',
            'email' => 'member@example.com',
            'password' => 'existing-hash',
            'is_active' => true,
        ]);

        $this->artisan('admin:copy-user-to-admin', ['email' => 'member@example.com'])
            ->expectsOutputToContain('An administrator with that email address already exists.')
            ->assertFailed();
    }
}
