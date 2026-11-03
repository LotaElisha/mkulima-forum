<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearbyAgronomistsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
    }

    private function agronomist(string $name, string $region, ?float $lat, ?float $lng, string $status = 'verified'): ServiceProvider
    {
        $user = User::provision([
            'tenant_id' => $this->tenant->id,
            'name' => $name,
            'phone' => '2557'.random_int(10000000, 99999999),
            'role' => 'farmer',
        ]);

        return ServiceProvider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'service_type' => 'agronomist',
            'business_name' => $name,
            'specializations' => ['mahindi'],
            'region' => $region,
            'latitude' => $lat,
            'longitude' => $lng,
            'verification_status' => $status,
            'consultation_fee' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_agronomists_are_sorted_by_distance_within_the_radius(): void
    {
        // Morogoro town, Dodoma (~210 km away), Arusha (~350 km away)
        $this->agronomist('Morogoro Agro', 'Morogoro', -6.8210, 37.6610);
        $this->agronomist('Dodoma Agro', 'Dodoma', -6.1630, 35.7516);
        $this->agronomist('Arusha Agro', 'Arusha', -3.3869, 36.6830);

        $response = $this->getJson('/api/v1/agronomists?lat=-6.80&lng=37.65&radius_km=250')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Morogoro Agro')
            ->assertJsonPath('data.1.name', 'Dodoma Agro');

        $this->assertLessThan(5, $response->json('data.0.distance_km'));
        $this->assertGreaterThan(150, $response->json('data.1.distance_km'));
    }

    public function test_without_location_the_list_falls_back_to_region(): void
    {
        $this->agronomist('Mbeya One', 'Mbeya', null, null);
        $this->agronomist('Iringa One', 'Iringa', -7.77, 35.69);

        $this->getJson('/api/v1/agronomists?region=Mbeya')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Mbeya One')
            ->assertJsonPath('data.0.distance_km', null);
    }

    public function test_an_agronomist_without_coordinates_is_still_found_in_the_farmers_region(): void
    {
        $this->agronomist('No GPS Agro', 'Morogoro', null, null);

        $this->getJson('/api/v1/agronomists?lat=-6.80&lng=37.65&region=Morogoro')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_unverified_agronomists_and_contact_details_are_never_listed(): void
    {
        $this->agronomist('Pending Agro', 'Morogoro', -6.82, 37.66, 'pending');
        $verified = $this->agronomist('Verified Agro', 'Morogoro', -6.82, 37.66);

        $response = $this->getJson('/api/v1/agronomists?region=Morogoro')->assertOk()->assertJsonPath('meta.total', 1);
        $this->assertArrayNotHasKey('phone', $response->json('data.0'));
        $this->assertArrayNotHasKey('license_number', $response->json('data.0'));

        $this->getJson('/api/v1/agronomists/'.$verified->uuid)->assertOk()->assertJsonPath('data.verified', true);
        $pending = ServiceProvider::where('verification_status', 'pending')->first();
        $this->getJson('/api/v1/agronomists/'.$pending->uuid)->assertNotFound()->assertJsonStructure(['message', 'errors']);
    }

    public function test_coordinates_must_come_as_a_valid_pair(): void
    {
        $this->getJson('/api/v1/agronomists?lat=-6.8')->assertUnprocessable();
        $this->getJson('/api/v1/agronomists?lat=200&lng=37')->assertUnprocessable();
        $this->getJson('/api/v1/agronomists?lat=-6.8&lng=37.6&radius_km=5000')->assertUnprocessable();
    }
}
