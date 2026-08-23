<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pins the field name the app must send to start a thread.
 *
 * The Flutter client sent `category_id`; the endpoint requires
 * `forum_category_id`. Every attempt to start a thread failed with a 422 that
 * the farmer saw as a raw error. Nothing on either side was wrong in
 * isolation, which is exactly why it survived - so the contract is asserted
 * here rather than left to agreement.
 */
class ForumThreadContractTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;

    private ForumCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS',
        ]);

        $this->farmer = User::provision([
            'tenant_id' => $tenant->id, 'name' => 'Mkulima',
            'phone' => '255710000201', 'role' => Roles::FARMER, 'status' => 'active',
        ]);

        $this->category = ForumCategory::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mazao',
            'slug' => 'mazao',
        ]);
    }

    public function test_a_farmer_can_start_a_thread(): void
    {
        Sanctum::actingAs($this->farmer);

        $response = $this->postJson('/api/forum/threads', [
            'forum_category_id' => (string) $this->category->id,
            'title' => 'Mbegu bora za mahindi Njombe',
            'body' => 'Ni mbegu gani inafaa zaidi kwa Njombe msimu huu?',
        ])->assertCreated();

        $this->assertDatabaseHas('forum_threads', [
            'title' => 'Mbegu bora za mahindi Njombe',
            'user_id' => $this->farmer->id,
        ]);

        // The client reads response.data['thread'].
        $response->assertJsonStructure(['message', 'thread' => ['uuid', 'title', 'body']]);
    }

    public function test_the_id_is_accepted_as_a_string(): void
    {
        // The Flutter screen carries the category id as a String all the way
        // through; if the endpoint ever required a strict integer this would
        // break again in the same invisible way.
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/forum/threads', [
            'forum_category_id' => (string) $this->category->id,
            'title' => 'Swali kuhusu mbolea',
            'body' => 'Mbolea gani ni bora kwa udongo wa kichanga?',
        ])->assertCreated();
    }

    public function test_the_old_field_name_is_still_rejected(): void
    {
        // Documents the failure rather than quietly accepting both spellings:
        // accepting `category_id` too would hide the next drift.
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/forum/threads', [
            'category_id' => $this->category->id,
            'title' => 'Kichwa',
            'body' => 'Maelezo ya kutosha kwa mada hii.',
        ])->assertStatus(422)->assertJsonValidationErrors('forum_category_id');
    }
}
