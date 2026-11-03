<?php

namespace Tests\Feature;

use App\Models\EquipmentListing;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EquipmentRentalTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private User $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        $this->owner = $this->user('Mwenye Trekta', '255711000001');
        $this->farmer = $this->user('Mkulima', '255711000002');
    }

    private function user(string $name, string $phone): User
    {
        return User::provision(['tenant_id' => $this->tenant->id, 'name' => $name, 'phone' => $phone, 'role' => 'farmer']);
    }

    private function listing(array $overrides = []): EquipmentListing
    {
        return EquipmentListing::create($overrides + [
            'tenant_id' => $this->tenant->id,
            'owner_id' => $this->owner->id,
            'category' => 'tractor',
            'title' => 'Trekta Massey Ferguson 375',
            'daily_rate' => 120000,
            'with_operator' => true,
            'region' => 'Morogoro',
            'latitude' => -6.82,
            'longitude' => 37.66,
            'status' => 'active',
        ]);
    }

    public function test_owner_lists_equipment_and_farmers_find_it_nearby(): void
    {
        Sanctum::actingAs($this->owner);
        $this->postJson('/api/v1/equipment', [
            'category' => 'water_pump',
            'title' => 'Pampu ya maji 3"',
            'daily_rate' => 25000,
            'region' => 'Morogoro',
            'lat' => -6.83,
            'lng' => 37.67,
        ])->assertCreated()->assertJsonPath('data.category_label', 'Pampu ya maji');

        $this->listing(['title' => 'Far tractor', 'region' => 'Arusha', 'latitude' => -3.38, 'longitude' => 36.68]);

        $response = $this->getJson('/api/v1/equipment?lat=-6.80&lng=37.65')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Pampu ya maji 3"');
        $this->assertArrayNotHasKey('phone', $response->json('data.0.owner'));

        $this->getJson('/api/v1/equipment?category=tractor&region=Arusha')->assertJsonPath('meta.total', 1);
    }

    public function test_listing_requires_an_account_and_valid_fields(): void
    {
        $this->postJson('/api/v1/equipment', [])->assertUnauthorized();

        Sanctum::actingAs($this->owner);
        $this->postJson('/api/v1/equipment', ['category' => 'spaceship', 'title' => 'x', 'daily_rate' => 0, 'region' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category', 'title', 'daily_rate', 'region']);
    }

    public function test_rental_request_accept_and_contact_reveal(): void
    {
        $listing = $this->listing();

        Sanctum::actingAs($this->farmer);
        $rental = $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'message' => 'Kulima ekari 5',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'requested')
            ->assertJsonPath('data.days', 3)
            ->assertJsonPath('data.quoted_total', 360000)
            ->assertJsonPath('data.counterpart.phone', null)
            ->json('data');

        // The renter cannot accept their own request.
        $this->postJson("/api/v1/rentals/{$rental['uuid']}/accept")->assertForbidden()->assertJsonStructure(['message', 'errors']);

        Sanctum::actingAs($this->owner);
        $this->getJson('/api/v1/me/rentals?as=owner')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.role', 'owner');
        $this->postJson("/api/v1/rentals/{$rental['uuid']}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.counterpart.phone', '255711000002');

        Sanctum::actingAs($this->farmer);
        $this->getJson('/api/v1/me/rentals')->assertJsonPath('data.0.counterpart.phone', '255711000001');
    }

    public function test_overlapping_dates_cannot_both_be_accepted(): void
    {
        $listing = $this->listing();
        $other = $this->user('Mkulima Wa Pili', '255711000003');

        Sanctum::actingAs($this->farmer);
        $first = $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ])->json('data.uuid');

        Sanctum::actingAs($other);
        $second = $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDays(6)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
        ])->assertCreated()->json('data.uuid');

        Sanctum::actingAs($this->owner);
        $this->postJson("/api/v1/rentals/{$first}/accept")->assertOk();
        $this->postJson("/api/v1/rentals/{$second}/accept")->assertUnprocessable();

        // A new request for held days is refused up front.
        Sanctum::actingAs($other);
        $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ])->assertUnprocessable();
    }

    public function test_owner_cannot_rent_own_equipment_and_dates_are_bounded(): void
    {
        $listing = $this->listing();

        Sanctum::actingAs($this->owner);
        $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ])->assertUnprocessable();

        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ])->assertUnprocessable();
        $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
        ])->assertUnprocessable();
    }

    public function test_paused_and_hidden_listings_are_not_shown_and_cannot_be_rented(): void
    {
        $paused = $this->listing(['status' => 'paused']);
        $hidden = $this->listing(['status' => 'hidden', 'title' => 'Hidden one']);

        $this->getJson('/api/v1/equipment')->assertJsonPath('meta.total', 0);
        $this->getJson("/api/v1/equipment/{$paused->uuid}")->assertNotFound();

        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/v1/equipment/{$hidden->uuid}/rentals", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ])->assertUnprocessable();

        // The owner can relist a paused listing, but not one an admin hid.
        Sanctum::actingAs($this->owner);
        $this->patchJson("/api/v1/equipment/{$paused->uuid}/status", ['status' => 'active'])->assertOk();
        $this->patchJson("/api/v1/equipment/{$hidden->uuid}/status", ['status' => 'active'])->assertNotFound();

        // Someone else cannot touch the owner's listing.
        Sanctum::actingAs($this->farmer);
        $this->patchJson("/api/v1/equipment/{$paused->uuid}/status", ['status' => 'paused'])->assertNotFound();
    }

    public function test_renter_can_cancel_and_a_decided_rental_cannot_change_again(): void
    {
        $listing = $this->listing();

        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson("/api/v1/equipment/{$listing->uuid}/rentals", [
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ])->json('data.uuid');

        $this->postJson("/api/v1/rentals/{$uuid}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');

        Sanctum::actingAs($this->owner);
        $this->postJson("/api/v1/rentals/{$uuid}/accept")->assertUnprocessable();
        $this->postJson("/api/v1/rentals/{$uuid}/explode")->assertNotFound();
    }
}
