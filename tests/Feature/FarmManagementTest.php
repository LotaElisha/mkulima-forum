<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmManagementTest extends TestCase
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

    public function test_farmer_can_create_and_list_farms(): void
    {
        $farmer = User::factory()->create([
            'role' => 'farmer',
        ]);

        $response = $this->actingAs($farmer, 'sanctum')->postJson('/api/farms', [
            'name' => 'Shamba la Mahindi Mbeya',
            'location' => 'Mbeya Vijijini',
            'size_acres' => 5.5,
            'crop_type' => 'Mahindi',
            'soil_type' => 'Loam',
            'planting_date' => '2026-03-01',
            'status' => 'active',
            'notes' => 'Tofauti ya mbolea ya DAP na Urea',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('farm.name', 'Shamba la Mahindi Mbeya');

        $listResponse = $this->actingAs($farmer, 'sanctum')->getJson('/api/farms');
        $listResponse->assertStatus(200)
            ->assertJsonPath('summary.total_farms', 1);
    }

    public function test_farmer_can_add_activity_log(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::create([
            'user_id' => $farmer->id,
            'tenant_id' => $farmer->tenant_id,
            'name' => 'Shamba la Mpunga Kilombero',
            'location' => 'Morogoro',
            'size_acres' => 3.0,
            'crop_type' => 'Mpunga',
            'status' => 'active',
        ]);

        $activityResponse = $this->actingAs($farmer, 'sanctum')->postJson("/api/farms/{$farm->uuid}/activities", [
            'activity_type' => 'Kuweka Mbolea',
            'activity_date' => '2026-04-15',
            'cost_tzs' => 120000,
            'notes' => 'Mfuko 2 wa Urea',
        ]);

        $activityResponse->assertStatus(201)
            ->assertJsonPath('activity.activity_type', 'Kuweka Mbolea');

        $showResponse = $this->actingAs($farmer, 'sanctum')->getJson("/api/farms/{$farm->uuid}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('stats.total_activities', 1)
            ->assertJsonPath('stats.total_expenditure', 120000);
    }
}
