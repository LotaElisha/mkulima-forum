<?php

namespace Tests\Feature;

use App\Models\BotConversation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MkulimaBotTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS',
        ]);

        $this->farmer = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Mkulima Mfano',
            'phone' => '255710000001', 'role' => 'farmer',
        ]);
    }

    private function fakeGemini(string $reply = 'Panda mahindi mwanzoni mwa msimu wa mvua.'): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => $reply]]],
                ]],
            ]),
        ]);
    }

    public function test_chat_requires_auth(): void
    {
        $this->postJson('/api/bot/chat', ['message' => 'Habari'])
            ->assertStatus(401);
    }

    public function test_chat_creates_conversation_and_persists_exchange(): void
    {
        $this->fakeGemini();
        Sanctum::actingAs($this->farmer);

        $resp = $this->postJson('/api/bot/chat', [
            'message' => 'Nipande mahindi lini Mbeya?',
            'region' => 'Mbeya',
        ])->assertOk();

        $uuid = $resp->json('conversation_uuid');
        $this->assertNotNull($uuid);
        $this->assertStringContainsString('mahindi', $resp->json('reply'));

        $conversation = BotConversation::where('uuid', $uuid)->first();
        $this->assertEquals($this->farmer->id, $conversation->user_id);
        $this->assertEquals(2, $conversation->messages()->count()); // user + model
        $this->assertEquals('sw', $conversation->language);
    }

    public function test_chat_continues_existing_conversation_with_history(): void
    {
        $this->fakeGemini();
        Sanctum::actingAs($this->farmer);

        $uuid = $this->postJson('/api/bot/chat', ['message' => 'Swali la kwanza'])
            ->json('conversation_uuid');

        $this->postJson('/api/bot/chat', [
            'message' => 'Swali la pili',
            'conversation_uuid' => $uuid,
        ])->assertOk()->assertJsonPath('conversation_uuid', $uuid);

        $conversation = BotConversation::where('uuid', $uuid)->first();
        $this->assertEquals(4, $conversation->messages()->count());

        // Second Gemini call must include prior history as multi-turn contents
        Http::assertSent(function ($request) {
            $contents = $request->data()['contents'] ?? [];
            return count($contents) >= 3; // first user + first model + new user
        });
    }

    public function test_users_cannot_read_others_conversations(): void
    {
        $this->fakeGemini();
        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson('/api/bot/chat', ['message' => 'Siri yangu'])
            ->json('conversation_uuid');

        $other = User::create([
            'tenant_id' => $this->farmer->tenant_id, 'name' => 'Mwingine',
            'phone' => '255710000002', 'role' => 'farmer',
        ]);
        Sanctum::actingAs($other);

        $this->getJson("/api/bot/conversations/{$uuid}")->assertStatus(404);
        $this->postJson('/api/bot/chat', [
            'message' => 'Naingilia', 'conversation_uuid' => $uuid,
        ])->assertStatus(404);
    }

    public function test_returns_503_without_api_key_and_persists_nothing(): void
    {
        config(['services.gemini.api_key' => null]);
        Sanctum::actingAs($this->farmer);

        $this->postJson('/api/bot/chat', ['message' => 'Habari'])
            ->assertStatus(503);
        $this->assertEquals(0, BotConversation::count() === 0 ? 0 : BotConversation::first()->messages()->count());
    }

    public function test_failed_gemini_turn_persists_no_messages(): void
    {
        config(['services.gemini.api_key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([], 500),
        ]);
        Sanctum::actingAs($this->farmer);

        $uuid = null;
        $this->postJson('/api/bot/chat', ['message' => 'Habari'])->assertStatus(503);

        // Conversation may exist but must hold no messages
        $conversation = BotConversation::first();
        if ($conversation) {
            $this->assertEquals(0, $conversation->messages()->count());
        }
    }

    public function test_conversation_list_and_delete(): void
    {
        $this->fakeGemini();
        Sanctum::actingAs($this->farmer);
        $uuid = $this->postJson('/api/bot/chat', ['message' => 'Habari yako'])
            ->json('conversation_uuid');

        $this->getJson('/api/bot/conversations')
            ->assertOk()->assertJsonCount(1, 'conversations');

        $this->deleteJson("/api/bot/conversations/{$uuid}")->assertOk();
        $this->getJson('/api/bot/conversations')
            ->assertOk()->assertJsonCount(0, 'conversations');
    }
}
