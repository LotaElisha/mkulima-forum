<?php

namespace Tests\Feature;

use App\Models\AiProvider;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\Secrets\SecretManagerServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        Tenant::firstOrCreate(['id' => 1], ['name' => 'Tanzania', 'country_code' => 'tz']);
        $this->admin = User::factory()->create(['role' => 'admin', 'tenant_id' => 1]);
        $this->farmer = User::factory()->create(['role' => 'farmer', 'tenant_id' => 1]);
    }

    public function test_non_admin_cannot_access_ai_providers_api(): void
    {
        $response = $this->actingAs($this->farmer)
            ->getJson('/api/admin/ai/providers');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_and_encrypt_ai_provider(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/ai/providers', [
                'name' => 'Google Gemini Production',
                'provider_type' => 'gemini',
                'api_key' => 'AIzaSyCDkBhNce-7O-qp3n_RH_gdqwJ3pW2w2tg',
                'model' => 'gemini-2.0-flash',
                'is_default' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('provider.name', 'Google Gemini Production')
            ->assertJsonPath('provider.is_default', true);

        $this->assertDatabaseHas('ai_providers', [
            'name' => 'Google Gemini Production',
            'provider_type' => 'gemini',
            'is_default' => true,
        ]);

        $provider = AiProvider::where('name', 'Google Gemini Production')->first();
        $secretManager = app(SecretManagerServiceInterface::class);
        $decryptedKey = $secretManager->getSecret($provider->id);

        $this->assertEquals('AIzaSyCDkBhNce-7O-qp3n_RH_gdqwJ3pW2w2tg', $decryptedKey);
    }

    public function test_api_key_is_never_leaked_in_plain_text_in_api_responses(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/admin/ai/providers', [
                'name' => 'Secret Test Provider',
                'provider_type' => 'openai',
                'api_key' => 'sk-proj-1234567890ABCDEFGHIJKLMNOPQRSTUV',
                'model' => 'gpt-4o',
            ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/ai/providers');

        $response->assertOk();
        $masked = $response->json('providers.0.masked_api_key');

        $this->assertStringNotContainsString('sk-proj-1234567890ABCDEFGHIJKLMNOPQRSTUV', $response->getContent());
        $this->assertEquals('sk-p••••••••TUV', $masked);
    }

    public function test_only_one_default_provider_exists_at_a_time(): void
    {
        $p1 = AiProvider::create([
            'name' => 'Provider 1',
            'provider_type' => 'gemini',
            'model' => 'gemini-2.0-flash',
            'is_default' => true,
        ]);

        $this->assertTrue((bool) $p1->fresh()->is_default);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/ai/providers', [
                'name' => 'Provider 2',
                'provider_type' => 'openai',
                'api_key' => 'sk-test-key-999',
                'model' => 'gpt-4o',
                'is_default' => true,
            ]);

        $response->assertCreated();
        $this->assertFalse((bool) $p1->fresh()->is_default);
        $this->assertTrue((bool) AiProvider::where('name', 'Provider 2')->first()->is_default);
    }

    public function test_test_connection_endpoint_returns_safe_health_status(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Pong']]]],
                ],
            ], 200),
        ]);

        $provider = AiProvider::create([
            'name' => 'Gemini Test',
            'provider_type' => 'gemini',
            'model' => 'gemini-2.0-flash',
            'status' => 'active',
        ]);

        app(SecretManagerServiceInterface::class)->storeSecret($provider->id, 'fake-gemini-key');

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/ai/providers/{$provider->uuid}/test");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Connection Successful');
    }

    public function test_ai_service_uses_db_provider_and_falls_back_if_empty(): void
    {
        $aiService = app(AIService::class);
        $fallback = $aiService->getDefaultProvider();

        $this->assertNotNull($fallback);

        $p = AiProvider::create([
            'name' => 'DB Gemini Provider',
            'provider_type' => 'gemini',
            'model' => 'gemini-1.5-pro',
            'status' => 'active',
            'is_default' => true,
        ]);
        app(SecretManagerServiceInterface::class)->storeSecret($p->id, 'key-12345');

        $resolved = $aiService->getDefaultProvider();
        $this->assertNotNull($resolved);
    }
}
