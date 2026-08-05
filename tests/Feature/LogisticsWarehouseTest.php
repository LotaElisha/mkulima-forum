<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Transporter;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogisticsWarehouseTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $farmer;
    private User $driver;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Tanzania',
            'country_code' => 'tz',
            'currency' => 'TZS',
        ]);

        $this->farmer = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mkulima Mfano',
            'phone' => '255710000001',
            'role' => 'farmer',
        ]);

        $this->driver = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dereva Mfano',
            'phone' => '255710000002',
            'role' => 'logistics',
        ]);

        $this->operator = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mmiliki Ghala',
            'phone' => '255710000003',
            'role' => 'seller',
        ]);
    }

    private function makeVerifiedTransporter(): Transporter
    {
        return Transporter::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->driver->id,
            'vehicle_type' => 'canter',
            'capacity_kg' => 3000,
            'base_region' => 'Mbeya',
            'verification_status' => 'verified',
        ]);
    }

    private function makeVerifiedWarehouse(float $capacity = 100): Warehouse
    {
        return Warehouse::create([
            'tenant_id' => $this->tenant->id,
            'operator_id' => $this->operator->id,
            'name' => 'Ghala la Mbeya',
            'storage_type' => 'dry',
            'region' => 'Mbeya',
            'capacity_tons' => $capacity,
            'available_tons' => $capacity,
            'price_per_ton_month' => 10000, // TZS
            'verification_status' => 'verified',
        ]);
    }

    // ── Logistics ──────────────────────────────────────────────

    public function test_transporter_directory_only_lists_verified_available(): void
    {
        $this->makeVerifiedTransporter();
        Transporter::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->operator->id,
            'vehicle_type' => 'lorry',
            'base_region' => 'Arusha',
            'verification_status' => 'pending',
        ]);

        $this->getJson('/api/logistics/transporters')
            ->assertOk()
            ->assertJsonCount(1, 'transporters')
            ->assertJsonPath('transporters.0.vehicle_type', 'canter');
    }

    public function test_full_freight_lifecycle_with_rating(): void
    {
        $transporter = $this->makeVerifiedTransporter();

        // Farmer posts a freight request
        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson('/api/logistics/freight', [
            'pickup_location' => 'Mbeya mjini',
            'dropoff_location' => 'Soko la Mwanjelwa',
            'cargo_weight_kg' => 800,
            'cargo_description' => 'Magunia 10 ya mahindi',
        ])->assertCreated()->json('freight.uuid');

        // Driver sees it on the open board and quotes
        Sanctum::actingAs($this->driver);
        $this->getJson('/api/logistics/freight?as=transporter')
            ->assertOk()->assertJsonCount(1, 'freight');
        $this->postJson("/api/logistics/freight/{$uuid}/quote", ['quoted_fare' => 45000])
            ->assertOk()->assertJsonPath('freight.status', 'quoted');

        // Second quote attempt fails (no longer open)
        $this->postJson("/api/logistics/freight/{$uuid}/quote", ['quoted_fare' => 40000])
            ->assertStatus(422);

        // Farmer accepts; driver cannot accept
        Sanctum::actingAs($this->driver);
        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'accepted'])
            ->assertStatus(422);
        Sanctum::actingAs($this->farmer);
        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'accepted'])
            ->assertOk()->assertJsonPath('freight.status', 'accepted');

        // Driver moves through transit to delivered
        Sanctum::actingAs($this->driver);
        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'in_transit'])->assertOk();
        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'delivered'])->assertOk();

        // Farmer rates once; transporter aggregate updates
        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/logistics/freight/{$uuid}/rate", ['rating' => 5])->assertOk();
        $this->postJson("/api/logistics/freight/{$uuid}/rate", ['rating' => 1])->assertStatus(422);

        $transporter->refresh();
        $this->assertEquals(5.0, (float) $transporter->rating);
        $this->assertEquals(1, $transporter->rating_count);
    }

    public function test_unverified_transporter_cannot_quote(): void
    {
        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson('/api/logistics/freight', [
            'pickup_location' => 'A', 'dropoff_location' => 'B',
        ])->json('freight.uuid');

        Sanctum::actingAs($this->driver); // no transporter profile
        $this->postJson("/api/logistics/freight/{$uuid}/quote", ['quoted_fare' => 10000])
            ->assertStatus(403);
    }

    public function test_farmer_can_cancel_open_freight_but_not_in_transit(): void
    {
        $this->makeVerifiedTransporter();
        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson('/api/logistics/freight', [
            'pickup_location' => 'A', 'dropoff_location' => 'B',
        ])->json('freight.uuid');

        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'cancelled'])
            ->assertOk()->assertJsonPath('freight.status', 'cancelled');

        // Cancelled → accepted not allowed
        $this->putJson("/api/logistics/freight/{$uuid}", ['status' => 'accepted'])
            ->assertStatus(422);
    }

    // ── Warehouse ──────────────────────────────────────────────

    public function test_warehouse_directory_filters_and_show(): void
    {
        $wh = $this->makeVerifiedWarehouse();
        $this->getJson('/api/warehouses?region=Mbeya&storage_type=dry')
            ->assertOk()->assertJsonCount(1, 'warehouses');
        $this->getJson('/api/warehouses?region=Arusha')
            ->assertOk()->assertJsonCount(0, 'warehouses');
        $this->getJson("/api/warehouses/{$wh->uuid}")
            ->assertOk()->assertJsonPath('warehouse.name', 'Ghala la Mbeya');
    }

    public function test_booking_lifecycle_with_capacity_accounting(): void
    {
        $wh = $this->makeVerifiedWarehouse(100);

        // Farmer books 40t for ~2 months → 40 × 10,000 × 2 = 800,000 TZS
        Sanctum::actingAs($this->farmer);
        $resp = $this->postJson('/api/warehouses/bookings', [
            'warehouse_uuid' => $wh->uuid,
            'produce_type' => 'mahindi',
            'quantity_tons' => 40,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(55)->toDateString(),
        ])->assertCreated();
        $uuid = $resp->json('booking.uuid');
        $this->assertEquals(800000, (float) $resp->json('booking.total_cost'));

        // Pending booking does not reserve capacity yet
        $this->assertEquals(100, (float) $wh->fresh()->available_tons);

        // Operator confirms → capacity reserved
        Sanctum::actingAs($this->operator);
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'confirmed'])->assertOk();
        $this->assertEquals(60, (float) $wh->fresh()->available_tons);

        // stored → withdrawn releases capacity
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'stored'])->assertOk();
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'withdrawn'])->assertOk();
        $this->assertEquals(100, (float) $wh->fresh()->available_tons);
    }

    public function test_overbooking_rejected_and_farmer_cancel_releases_capacity(): void
    {
        $wh = $this->makeVerifiedWarehouse(50);

        Sanctum::actingAs($this->farmer);
        // Too big
        $this->postJson('/api/warehouses/bookings', [
            'warehouse_uuid' => $wh->uuid,
            'produce_type' => 'mpunga',
            'quantity_tons' => 80,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ])->assertStatus(422);

        // Fits
        $uuid = $this->postJson('/api/warehouses/bookings', [
            'warehouse_uuid' => $wh->uuid,
            'produce_type' => 'mpunga',
            'quantity_tons' => 30,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ])->assertCreated()->json('booking.uuid');

        Sanctum::actingAs($this->operator);
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'confirmed'])->assertOk();
        $this->assertEquals(20, (float) $wh->fresh()->available_tons);

        // Farmer cancels confirmed booking → capacity released
        Sanctum::actingAs($this->farmer);
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'cancelled'])->assertOk();
        $this->assertEquals(50, (float) $wh->fresh()->available_tons);

        // Operator cannot cancel; farmer cannot confirm
        $this->putJson("/api/warehouses/bookings/{$uuid}", ['status' => 'confirmed'])->assertStatus(422);
    }

    public function test_operator_cannot_book_own_warehouse(): void
    {
        $wh = $this->makeVerifiedWarehouse();
        Sanctum::actingAs($this->operator);
        $this->postJson('/api/warehouses/bookings', [
            'warehouse_uuid' => $wh->uuid,
            'produce_type' => 'korosho',
            'quantity_tons' => 5,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ])->assertStatus(422);
    }
}
