<?php

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards against reintroducing fabricated demo data (audit 2026-07-12):
 * drone bookings persist for real, IoT never returns invented sensors,
 * yield estimates are labelled reference values and saved to history,
 * notifications have genuine read-state, SMS send is admin-only.
 */
class HonestEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $farmer;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::firstOrCreate(['id' => 1], ['name' => 'Tanzania', 'country_code' => 'tz']);
        $this->farmer = User::factory()->create(['role' => 'farmer', 'tenant_id' => 1]);
        $this->admin = User::factory()->create(['role' => 'admin', 'tenant_id' => 1]);
        FeatureFlag::create(['key' => 'drone_services', 'name' => 'Drone', 'enabled' => true]);
        FeatureFlag::create(['key' => 'iot_sensors', 'name' => 'IoT', 'enabled' => false]);
    }

    public function test_drone_booking_persists_and_lists_only_own_real_bookings(): void
    {
        // Before booking anything the list must be empty — no demo bookings.
        $this->actingAs($this->farmer)->getJson('/api/drone/bookings')
            ->assertOk()
            ->assertJsonCount(0, 'bookings');

        $this->actingAs($this->farmer)->postJson('/api/drone/book', [
            'service_id' => 'spraying',
            'farm_location' => 'Kibaha, Pwani',
            'farm_size_acres' => 4,
            'preferred_date' => now()->addDays(3)->toDateString(),
            'contact_phone' => '255712000001',
        ])->assertCreated()
          ->assertJsonPath('booking.total_cost', 60000);

        $this->actingAs($this->farmer)->getJson('/api/drone/bookings')
            ->assertOk()
            ->assertJsonCount(1, 'bookings')
            ->assertJsonPath('bookings.0.farm_location', 'Kibaha, Pwani')
            ->assertJsonPath('bookings.0.status', 'pending');

        // Another user sees none of it.
        $this->actingAs($this->admin)->getJson('/api/drone/bookings')
            ->assertOk()
            ->assertJsonCount(0, 'bookings');
    }

    public function test_iot_endpoints_answer_honestly_when_disabled(): void
    {
        $this->getJson('/api/iot/sensors')->assertStatus(503);
        $this->actingAs($this->farmer)->getJson('/api/iot/my-sensors')->assertStatus(503);
    }

    public function test_yield_estimate_is_labelled_and_history_is_real(): void
    {
        $this->actingAs($this->farmer)->getJson('/api/yield/history')
            ->assertOk()
            ->assertJsonCount(0, 'history'); // no fabricated history

        $response = $this->actingAs($this->farmer)->postJson('/api/yield/estimate', [
            'crop_type' => 'mahindi',
            'farm_size_acres' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('method', 'reference_table')
            ->assertJsonPath('estimated_yield.total', 50);
        $this->assertNotEmpty($response->json('disclaimer'));
        $this->assertNull($response->json('confidence_score')); // no invented confidence

        $this->actingAs($this->farmer)->getJson('/api/yield/history')
            ->assertOk()
            ->assertJsonCount(1, 'history')
            ->assertJsonPath('history.0.crop_type', 'mahindi');
    }

    public function test_yield_photo_analysis_is_honest_not_fabricated(): void
    {
        $file = \Illuminate\Http\Testing\File::image('shamba.jpg');

        $this->actingAs($this->farmer)->postJson('/api/yield/analyze-photo', [
            'photo' => $file,
            'crop_type' => 'mahindi',
        ])->assertStatus(501);
    }

    public function test_notification_read_state_is_persistent(): void
    {
        // New user gets the welcome notification, unread.
        $first = $this->actingAs($this->farmer)->getJson('/api/notifications');
        $first->assertOk();
        $this->assertGreaterThan(0, $first->json('unread_count'));
        $id = $first->json('notifications.0.id');

        $this->actingAs($this->farmer)->postJson("/api/notifications/{$id}/read")->assertOk();

        $second = $this->actingAs($this->farmer)->getJson('/api/notifications');
        $this->assertTrue($second->json('notifications.0.read'));
        $this->assertSame($first->json('unread_count') - 1, $second->json('unread_count'));

        $this->actingAs($this->farmer)->postJson('/api/notifications/read-all')->assertOk();
        $this->assertSame(0, $this->actingAs($this->farmer)->getJson('/api/notifications')->json('unread_count'));
    }

    public function test_sms_send_is_admin_only(): void
    {
        $payload = ['phone' => '255712000002', 'message' => 'Habari'];

        $this->actingAs($this->farmer)->postJson('/api/sms/send', $payload)->assertForbidden();
        $this->actingAs($this->admin)->postJson('/api/sms/send', $payload)->assertOk();
    }
}
