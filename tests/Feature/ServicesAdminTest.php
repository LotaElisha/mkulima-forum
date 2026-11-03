<?php

namespace Tests\Feature;

use App\Filament\Resources\AgronomistResource\Pages\ListAgronomists;
use App\Filament\Resources\EquipmentListingResource\Pages\ListEquipmentListings;
use App\Models\EquipmentListing;
use App\Models\ServiceProvider;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServicesAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_verifies_an_agronomist_and_hides_a_listing(): void
    {
        $tenant = Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $owner = User::provision(['tenant_id' => $tenant->id, 'name' => 'Agro', 'phone' => '255712000001', 'role' => 'farmer']);

        $provider = ServiceProvider::create([
            'tenant_id' => $tenant->id, 'user_id' => $owner->id, 'service_type' => 'agronomist',
            'region' => 'Morogoro', 'verification_status' => 'pending', 'is_active' => true,
        ]);
        $listing = EquipmentListing::create([
            'tenant_id' => $tenant->id, 'owner_id' => $owner->id, 'category' => 'tractor',
            'title' => 'Trekta', 'daily_rate' => 100000, 'region' => 'Morogoro', 'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/admin/agronomists')->assertOk()->assertSee('Morogoro');
        $this->actingAs($admin)->get('/admin/equipment-listings')->assertOk()->assertSee('Trekta');

        $this->getJson('/api/v1/agronomists')->assertJsonPath('meta.total', 0);
        Livewire::actingAs($admin)->test(ListAgronomists::class)->callTableAction('verify', $provider);
        $this->assertSame('verified', $provider->fresh()->verification_status);
        $this->getJson('/api/v1/agronomists')->assertJsonPath('meta.total', 1);

        Livewire::actingAs($admin)->test(ListEquipmentListings::class)->callTableAction('hide', $listing);
        $this->assertSame('hidden', $listing->fresh()->status);
    }

    public function test_farmers_cannot_open_the_services_admin(): void
    {
        Tenant::create(['name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $this->actingAs($farmer)->get('/admin/agronomists')->assertForbidden();
    }
}
