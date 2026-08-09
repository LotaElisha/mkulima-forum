<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::create([
            'id' => 1,
            'name' => 'Default Tenant',
            'slug' => 'default',
            'country_code' => 'TZ',
            'domain' => 'default.local',
        ]);
    }

    public function test_admin_can_onboard_new_agrodealer_and_supplier(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/vendors', [
            'name' => 'Kibo Seed & Agrovet Co.',
            'phone' => '255788123456',
            'email' => 'kibo@agrovet.co.tz',
            'role' => 'agrodealer',
            'store_name' => 'Kibo Agrodealer Branch #1',
            'store_location' => 'Arusha Town',
            'business_license' => 'TZ-AGR-99812',
            'store_description' => 'Suppliers of certified maize and bean seeds.',
            'kyc_status' => 'verified',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('vendor.name', 'Kibo Seed & Agrovet Co.')
            ->assertJsonPath('vendor.role', 'agrodealer');

        $this->assertDatabaseHas('users', [
            'phone' => '255788123456',
            'role' => 'agrodealer',
            'business_license' => 'TZ-AGR-99812',
        ]);
    }
}
