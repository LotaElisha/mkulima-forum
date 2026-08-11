<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OtpService;
use App\Services\Spine\ConfigRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        Cache::flush();
    }

    public function test_unconfigured_production_otp_fails_closed_without_leaking_code(): void
    {
        config(['app.debug' => false]);
        $this->app['env'] = 'production';

        $this->postJson('/api/auth/otp/request', ['phone' => '255712345678'])
            ->assertStatus(503)
            ->assertJsonMissingPath('dev_code');
    }

    public function test_otp_registration_creates_user_with_spatie_role(): void
    {
        $otp = app(OtpService::class)->generate('255712345678', 'register');

        $this->postJson('/api/auth/otp/verify', [
            'phone' => '255712345678',
            'code' => $otp['code'],
            'purpose' => 'register',
            'name' => 'Mkulima Mpya',
            'role' => 'agrodealer',
            'country_code' => 'tz',
        ])->assertOk()->assertJsonPath('user.role', 'agrodealer');

        $user = User::where('phone', '255712345678')->first();
        $this->assertTrue($user->hasRole('agrodealer'));
    }

    public function test_admin_can_disable_and_enable_otp_from_backend(): void
    {
        $admin = User::factory()->role('admin')->create();
        Sanctum::actingAs($admin);

        $this->putJson('/api/admin/settings/otp', ['enabled' => false])
            ->assertOk()->assertJsonPath('enabled', false);
        $this->postJson('/api/auth/otp/request', ['phone' => '255712345670'])
            ->assertStatus(503)->assertJsonPath('message', 'OTP authentication is disabled.');

        $this->putJson('/api/admin/settings/otp', ['enabled' => true])
            ->assertOk()->assertJsonPath('enabled', true);
        $this->getJson('/api/admin/settings/otp')->assertOk()->assertJsonPath('enabled', true);
    }

    public function test_otp_verification_locks_after_five_invalid_codes(): void
    {
        app(ConfigRegistry::class)->set('auth.otp_enabled', true, null, 'authentication', 'boolean');
        app(OtpService::class)->generate('255712345671', 'login');

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/auth/otp/verify', [
                'phone' => '255712345671', 'code' => '000000', 'purpose' => 'login',
            ])->assertStatus(422);
        }

        $this->postJson('/api/auth/otp/verify', [
            'phone' => '255712345671', 'code' => '000000', 'purpose' => 'login',
        ])->assertStatus(429);
    }

    public function test_farmer_web_login_uses_http_only_cookie_without_exposing_token(): void
    {
        $farmer = User::factory()->create([
            'email' => 'farmer-web@example.test',
            'password' => Hash::make('correct-horse-battery-staple'),
            'status' => 'active',
        ]);

        $this->withHeader('X-Auth-Client', 'web')->postJson('/api/auth/login/email', [
            'email' => $farmer->email,
            'password' => 'correct-horse-battery-staple',
        ])->assertOk()->assertCookie('user_token')->assertJsonMissingPath('token');
    }

    public function test_staff_roles_cannot_be_self_registered(): void
    {
        $otp = app(OtpService::class)->generate('255712345679', 'register');

        $this->postJson('/api/auth/otp/verify', [
            'phone' => '255712345679',
            'code' => $otp['code'],
            'purpose' => 'register',
            'name' => 'Mtu Mbaya',
            'role' => 'admin',
            'country_code' => 'tz',
        ])->assertUnprocessable();
    }

    public function test_admin_routes_reject_non_admins(): void
    {
        $farmer = User::factory()->create();
        Sanctum::actingAs($farmer);

        $this->getJson('/api/admin/dashboard')->assertForbidden();
    }

    public function test_admin_login_uses_http_only_cookie_and_does_not_expose_token(): void
    {
        $admin = User::factory()->role('admin')->create([
            'email' => 'secure-admin@example.com',
            'password' => Hash::make('correct-horse-battery-staple'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $admin->email,
            'password' => 'correct-horse-battery-staple',
        ]);

        $response->assertOk()
            ->assertJsonMissingPath('token')
            ->assertCookie('admin_token');
    }

    public function test_admin_routes_allow_admins(): void
    {
        $admin = User::factory()->role('admin')->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/dashboard')->assertOk();
    }

    public function test_seller_dashboard_rejects_farmers(): void
    {
        $farmer = User::factory()->create();
        Sanctum::actingAs($farmer);

        $this->getJson('/api/seller/dashboard')->assertForbidden();
    }

    public function test_users_cannot_modify_others_products(): void
    {
        $seller = User::factory()->role('agrodealer')->create();
        $intruder = User::factory()->create();

        $category = Category::create([
            'tenant_id' => 1, 'name' => 'Seeds', 'slug' => 'seeds-t', 'is_active' => true,
        ]);

        $product = Product::create([
            'tenant_id' => 1,
            'category_id' => $category->id,
            'user_id' => $seller->id,
            'uuid' => (string) Str::uuid(),
            'name' => 'Mbegu za Mahindi',
            'slug' => 'mbegu-mahindi',
            'price' => 15000,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        Sanctum::actingAs($intruder);
        $this->putJson("/api/marketplace/products/{$product->uuid}", ['price' => 1])
            ->assertNotFound(); // ownership scoping hides it entirely
    }
}
