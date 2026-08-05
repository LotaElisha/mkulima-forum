<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\ServiceProvider;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ForumAndServicesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $farmer;

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
    }

    private function makeThread(): ForumThread
    {
        $category = ForumCategory::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Wadudu na Magonjwa',
            'slug' => 'wadudu',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return ForumThread::create([
            'tenant_id' => $this->tenant->id,
            'forum_category_id' => $category->id,
            'user_id' => $this->farmer->id,
            'uuid' => (string) Str::uuid(),
            'title' => 'Mahindi yangu yana madoa',
            'body' => 'Nisaidieni tafadhali',
            'region' => 'Mbeya',
        ]);
    }

    public function test_upvote_is_toggleable_and_single_per_user(): void
    {
        $thread = $this->makeThread();
        Sanctum::actingAs($this->farmer);

        // First vote counts once even if called twice... first call: vote
        $this->postJson("/api/forum/threads/{$thread->uuid}/upvote")
            ->assertOk()
            ->assertJson(['voted' => true, 'upvote_count' => 1]);

        // Second call: toggle off, not increment
        $this->postJson("/api/forum/threads/{$thread->uuid}/upvote")
            ->assertOk()
            ->assertJson(['voted' => false, 'upvote_count' => 0]);
    }

    public function test_threads_filter_by_region(): void
    {
        $this->makeThread(); // Mbeya

        $this->getJson('/api/forum/threads?region=Mbeya')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1);

        $this->getJson('/api/forum/threads?region=Arusha')
            ->assertOk()
            ->assertJsonPath('pagination.total', 0);
    }

    public function test_service_booking_flow(): void
    {
        $providerUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dkt. Mifugo',
            'phone' => '255710000002',
            'role' => 'veterinary',
        ]);

        $provider = ServiceProvider::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $providerUser->id,
            'service_type' => 'veterinary',
            'region' => 'Dodoma',
            'verification_status' => 'verified',
            'consultation_fee' => 10000,
            'is_active' => true,
        ]);

        // Public directory
        $this->getJson('/api/services/providers?service_type=veterinary&region=Dodoma')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1);

        // Farmer books a consultation
        Sanctum::actingAs($this->farmer);
        $response = $this->postJson('/api/services/bookings', [
            'provider_uuid' => $provider->uuid,
            'booking_type' => 'consultation',
            'description' => "Ng'ombe wangu hali chakula",
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ])->assertCreated();

        $uuid = $response->json('booking.uuid');

        // Provider confirms
        Sanctum::actingAs($providerUser);
        $this->putJson("/api/services/bookings/{$uuid}", ['status' => 'confirmed'])
            ->assertOk();
        $this->putJson("/api/services/bookings/{$uuid}", ['status' => 'completed'])
            ->assertOk();

        // Farmer rates
        Sanctum::actingAs($this->farmer);
        $this->postJson("/api/services/bookings/{$uuid}/rate", ['rating' => 5])
            ->assertOk();

        $this->assertEquals(5.0, (float) $provider->fresh()->rating);
    }
}
