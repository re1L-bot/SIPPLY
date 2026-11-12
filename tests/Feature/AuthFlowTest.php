<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesAccountTables;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use CreatesAccountTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAccountTables();
        $this->withoutVite();
    }

    public function test_guest_can_open_login_and_registration_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to your account');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create my account');
    }

    public function test_registration_creates_a_hashed_user_and_signs_them_in(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => '  JAMIE@example.com ',
            'phone_number' => '09171234567',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'jamie@example.com')->firstOrFail();

        $this->assertSame('Jamie', $user->first_name);
        $this->assertSame('Rivera', $user->last_name);
        $this->assertSame('09171234567', $user->phone_number);
        $this->assertTrue(Hash::check('secure-pass-123', $user->password));
        $this->assertAuthenticatedAs($user);

        $this->get(route('account'))
            ->assertOk()
            ->assertSee('My account')
            ->assertSee('jamie@example.com');
    }

    public function test_inactive_customer_is_signed_out_of_existing_sessions(): void
    {
        $customer = User::factory()->inactive()->create();

        $this->actingAs($customer)
            ->get(route('account'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_rejects_an_existing_email_case_insensitively(): void
    {
        User::factory()->create(['email' => 'already@example.com']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Jamie',
                'last_name' => 'Rivera',
                'email' => 'ALREADY@example.com',
                'password' => 'secure-pass-123',
                'password_confirmation' => 'secure-pass-123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Jamie',
                'last_name' => 'Rivera',
                'email' => 'jamie@example.com',
                'password' => 'secure-pass-123',
                'password_confirmation' => 'different-pass-123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');
    }

    public function test_login_authenticates_an_active_user_and_records_last_login(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'plain-password',
        ]);

        $this->post(route('login.store'), [
            'email' => ' MEMBER@example.com ',
            'password' => 'plain-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_rejects_invalid_credentials_and_inactive_accounts(): void
    {
        $inactiveUser = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'plain-password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $inactiveUser->email,
                'password' => 'plain-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_account_requires_authentication_and_logout_ends_the_session(): void
    {
        $this->get(route('account'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
