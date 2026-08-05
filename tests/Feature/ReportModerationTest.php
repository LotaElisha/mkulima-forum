<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumThread;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;
    protected ForumThread $thread;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::firstOrCreate(['id' => 1], ['name' => 'Tanzania', 'country_code' => 'tz']);
        $this->admin = User::factory()->create(['role' => 'admin', 'tenant_id' => 1]);
        $this->farmer = User::factory()->create(['role' => 'farmer', 'tenant_id' => 1]);

        $author = User::factory()->create(['role' => 'farmer', 'tenant_id' => 1]);
        $category = ForumCategory::create([
            'tenant_id' => 1,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Kilimo cha Mahindi',
            'slug' => 'kilimo-cha-mahindi',
        ]);
        $this->thread = ForumThread::create([
            'tenant_id' => 1,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'forum_category_id' => $category->id,
            'user_id' => $author->id,
            'title' => 'Dawa ya miujiza inaponya kila ugonjwa',
            'body' => 'Nunua dawa hii, inaponya magonjwa yote ya mahindi kwa siku moja...',
            'status' => 'active',
        ]);
    }

    public function test_reporting_requires_auth(): void
    {
        $this->postJson('/api/reports', [
            'type' => 'forum_thread',
            'id' => $this->thread->uuid,
            'reason' => 'misleading',
        ])->assertUnauthorized();
    }

    public function test_farmer_can_report_and_duplicates_are_rejected(): void
    {
        $payload = [
            'type' => 'forum_thread',
            'id' => $this->thread->uuid,
            'reason' => 'misleading',
            'details' => 'Anadanganya wakulima kuhusu dawa isiyothibitishwa.',
        ];

        $this->actingAs($this->farmer)->postJson('/api/reports', $payload)->assertCreated();
        $this->actingAs($this->farmer)->postJson('/api/reports', $payload)->assertStatus(422);
    }

    public function test_admin_resolves_report_and_content_is_hidden_from_feed(): void
    {
        $this->actingAs($this->farmer)->postJson('/api/reports', [
            'type' => 'forum_thread',
            'id' => $this->thread->uuid,
            'reason' => 'misleading',
        ])->assertCreated();

        $list = $this->actingAs($this->admin)->getJson('/api/admin/reports?status=pending');
        $list->assertOk()->assertJsonCount(1, 'data');
        $reportUuid = $list->json('data.0.uuid');

        $this->actingAs($this->admin)
            ->postJson("/api/admin/reports/{$reportUuid}/resolve", [
                'action' => 'content_hidden',
                'notes' => 'Taarifa za uongo za kilimo.',
            ])
            ->assertOk();

        $this->assertSame('hidden', $this->thread->fresh()->status);

        // Hidden thread must vanish from the public feed
        $feed = $this->getJson('/api/forum/threads');
        $feed->assertOk();
        $this->assertNotContains(
            $this->thread->uuid,
            collect($feed->json('data') ?? $feed->json('threads') ?? [])->pluck('uuid')->all()
        );
    }

    public function test_non_admin_cannot_access_moderation_queue(): void
    {
        $this->actingAs($this->farmer)->getJson('/api/admin/reports')->assertForbidden();
    }

    public function test_dismiss_leaves_content_visible(): void
    {
        $this->actingAs($this->farmer)->postJson('/api/reports', [
            'type' => 'forum_thread',
            'id' => $this->thread->uuid,
            'reason' => 'spam',
        ])->assertCreated();

        $reportUuid = $this->actingAs($this->admin)
            ->getJson('/api/admin/reports')->json('data.0.uuid');

        $this->actingAs($this->admin)
            ->postJson("/api/admin/reports/{$reportUuid}/dismiss", ['notes' => 'Si spam.'])
            ->assertOk();

        $this->assertSame('active', $this->thread->fresh()->status);
    }
}
