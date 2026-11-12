<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderConfirmationMailer;
use App\Services\OrderWorkflow;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesDeliveryFeeTable;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class SecurityFeaturesTest extends TestCase
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
            $mock->shouldReceive('sendDelivered')->byDefault();
        });
    }

    /**
     * 1. Password hashing and strong password validation
     */
    public function test_password_is_securely_hashed_and_weak_passwords_are_rejected(): void
    {
        $password = 'SecretPassword123!';
        $user = User::factory()->create([
            'password' => $password,
        ]);

        // Verify password is not stored in plaintext
        $this->assertNotSame($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));

        // Short password rejected
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'weakpass@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $response->assertSessionHasErrors('password');
    }

    /**
     * 2. CSRF Protection
     */
    public function test_csrf_protection_blocks_requests_without_valid_tokens(): void
    {
        $this->startSession();

        // Instantiate CSRF middleware without testing bypass
        $middleware = new class(app(), app('encrypter')) extends ValidateCsrfToken
        {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $request = Request::create(route('login'), 'POST', [
            '_token' => 'invalid-or-forged-csrf-token',
            'email' => 'victim@example.com',
            'password' => 'secret123',
        ]);
        $request->setLaravelSession(session()->driver());

        $this->expectException(TokenMismatchException::class);
        $middleware->handle($request, fn () => response('OK'));
    }

    /**
     * 3. SQL Injection Prevention
     */
    public function test_sql_injection_payloads_are_safely_parameterized_and_blocked(): void
    {
        User::factory()->create([
            'email' => 'realuser@example.com',
            'password' => 'StrongPassword123',
        ]);

        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "realuser@example.com' --",
            "admin' /*",
            "' UNION SELECT * FROM users --",
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $response = $this->post(route('login'), [
                'email' => $payload,
                'password' => "' OR 1=1 --",
            ]);

            // Must never authenticate the user
            $this->assertGuest();
        }
    }

    /**
     * 4. XSS Prevention: Blade properly escapes malicious scripts
     */
    public function test_xss_payloads_are_escaped_in_rendered_html(): void
    {
        $maliciousPayload = '<script>alert("XSS")</script>';

        $user = User::factory()->create([
            'first_name' => $maliciousPayload,
        ]);

        $response = $this->actingAs($user)->get(route('account'));

        $response->assertOk();
        // The script tag must be converted to &lt;script&gt; or escaped, never rendered raw
        $response->assertDontSee($maliciousPayload, false);
        $response->assertSee(e($maliciousPayload), false);
    }

    /**
     * 5. Login Rate Limiting (Brute-Force Protection)
     */
    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login'), [
                'email' => 'attacker@example.com',
                'password' => 'wrong-guess',
            ]);
        }

        // 7th attempt must be throttled with HTTP 429 Too Many Requests
        $response = $this->post(route('login'), [
            'email' => 'attacker@example.com',
            'password' => 'wrong-guess',
        ]);

        $response->assertStatus(429);
    }

    /**
     * 6. Role-Based Access Control & Admin Route Protection
     */
    public function test_unauthenticated_and_regular_users_cannot_access_admin_routes(): void
    {
        // Unauthenticated guest is redirected to admin login
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        // Authenticated customer cannot access admin routes
        $customer = User::factory()->create();
        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    /**
     * 7. Server-Side Price Validation
     */
    public function test_server_side_price_validation_ignores_client_tampering(): void
    {
        $user = User::factory()->create();
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Tea',
            'is_active' => true,
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => 'Classic Milk Tea',
            'base_price' => 150.00,
            'is_available' => true,
            'stock_quantity' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentMethodId = DB::table('payment_methods')->insertGetId([
            'method_name' => 'Cash on Delivery',
            'is_active' => true,
        ]);

        // Add item to cart with legitimate price
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $user->user_id,
            'cart_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => 2,
            'unit_price' => 150.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Even if client tries to send price = 1.00 or tampered subtotal in POST
        $this->actingAs($user)->post(route('checkout.store'), [
            'recipient_name' => 'Jamie Rivera',
            'phone_number' => '09171234567',
            'address_line' => '123 Mango Street',
            'barangay' => 'Poblacion',
            'city' => 'Bantayan',
            'province' => 'Cebu',
            'payment_method_id' => $paymentMethodId,
            'unit_price' => 1.00,
            'grand_total' => 1.00,
            'subtotal' => 1.00,
        ])->assertRedirect(route('account'));

        // Server recalculates based on database base_price (150 * 2 = 300) plus delivery fee (100) = 400
        $order = DB::table('orders')->where('user_id', $user->user_id)->first();
        $this->assertEquals('300.00', $order->subtotal);
        $this->assertEquals('400.00', $order->grand_total);
    }

    /**
     * 8. Payment Verification: Status transitions and admin verification
     */
    public function test_payment_status_is_verified_and_updates_on_completion(): void
    {
        $user = User::factory()->create();
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $user->user_id,
            'order_number' => 'SP-TESTPAY1',
            'order_status' => 'confirmed',
            'subtotal' => 100,
            'grand_total' => 100,
            'recipient_name' => 'Jamie',
            'recipient_phone' => '09170000000',
            'delivery_address' => 'Sample Address',
            'placed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentMethodId = DB::table('payment_methods')->insertGetId([
            'method_name' => 'GCash',
            'is_active' => true,
        ]);

        DB::table('payments')->insert([
            'order_id' => $orderId,
            'payment_method_id' => $paymentMethodId,
            'amount' => 100.00,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // When order workflow transitions to completed, payment is verified and marked paid
        $workflow = app(OrderWorkflow::class);
        $workflow->transition($orderId, 'completed', adminId: 1, notes: 'Delivered and paid');

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'payment_status' => 'paid',
        ]);
        $this->assertNotNull(DB::table('payments')->where('order_id', $orderId)->value('paid_at'));
    }

    /**
     * 9. HTTPS, SSL & Security Headers
     */
    public function test_responses_contain_required_security_headers(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * 10. Secure Session and Cookie Management
     */
    public function test_session_cookie_is_configured_with_httponly_and_samesite(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));

        // Session ID is regenerated on authentication to prevent session fixation
        $user = User::factory()->create([
            'password' => 'Secret123456!',
        ]);

        $this->get(route('login'));
        $initialSessionId = session()->getId();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Secret123456!',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($initialSessionId, session()->getId());
    }

    /**
     * 11. Environment Variable & Sensitive File Protection
     */
    public function test_direct_access_to_env_and_sensitive_files_is_blocked(): void
    {
        $blockedPaths = [
            '/.env',
            '/.env.local',
            '/.env.production',
            '/.git',
            '/.git/config',
            '/singledb.sql',
            '/database.sql',
        ];

        foreach ($blockedPaths as $path) {
            $response = $this->get($path);
            $this->assertTrue(
                in_array($response->getStatusCode(), [403, 404]),
                "Path {$path} must be blocked with 403 or 404, got {$response->getStatusCode()}"
            );
        }
    }
}
