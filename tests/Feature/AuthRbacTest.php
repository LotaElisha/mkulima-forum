<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function test_otp_code_is_not_leaked_outside_debug(): void
    {
        config(['app.debug' => false]);
        $this->app['env'] = 'production';

        $this->postJson('/api/auth/otp/request', ['phone' => '255712345678'])
            ->assertOk()
            ->assertJsonMissingPath('dev_code');
    }

    public function test_otp_registration_creates_user_with_spatie_role(): void
    {
        $otp = app(\App\Services\OtpService::class)->generate('255712345678', 'register');

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

    public function test_staff_roles_cannot_be_self_registered(): void
    {
        $otp = app(\App\Services\OtpService::class)->generate('255712345679', 'register');

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

        $category = \App\Models\Category::create([
            'tenant_id' => 1, 'name' => 'Seeds', 'slug' => 'seeds-t', 'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'tenant_id' => 1,
            'category_id' => $category->id,
            'user_id' => $seller->id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
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
