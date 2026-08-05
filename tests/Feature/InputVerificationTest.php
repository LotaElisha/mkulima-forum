<?php

namespace Tests\Feature;

use App\Models\CounterfeitAlert;
use App\Models\RegisteredInput;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InputVerificationTest extends TestCase
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
    }

    protected function seedRegistry(): void
    {
        RegisteredInput::create([
            'name' => 'DuduAll 450 EC',
            'type' => 'insecticide',
            'registration_number' => 'TPRI/TEST/0001',
            'manufacturer' => 'Test Agro Ltd',
            'status' => 'registered',
            'source' => 'TPRI Test List',
        ]);
        RegisteredInput::create([
            'name' => 'KillFast 99',
            'type' => 'pesticide',
            'registration_number' => 'TPRI/TEST/0002',
            'status' => 'banned',
            'source' => 'TPRI Banned List',
        ]);
    }

    public function test_registry_lookup_finds_registered_product(): void
    {
        $this->seedRegistry();

        $response = $this->getJson('/api/inputs/verify?q=DuduAll');

        $response->assertOk()
            ->assertJsonPath('registry_ready', true)
            ->assertJsonPath('matches.0.name', 'DuduAll 450 EC')
            ->assertJsonPath('matches.0.status', 'registered');

        $this->assertStringContainsString('imepatikana', $response->json('guidance'));
    }

    public function test_banned_product_gets_strong_warning(): void
    {
        $this->seedRegistry();

        $response = $this->getJson('/api/inputs/verify?q=KillFast');

        $response->assertOk()->assertJsonPath('matches.0.status', 'banned');
        $this->assertStringContainsString('MARUFUKU', $response->json('guidance'));
    }

    public function test_unknown_product_is_flagged_as_risk_not_guessed(): void
    {
        $this->seedRegistry();

        $response = $this->getJson('/api/inputs/verify?q=FakeBrandX');

        $response->assertOk()->assertJsonCount(0, 'matches');
        $this->assertStringContainsString('HAIKUPATIKANA', $response->json('guidance'));
    }

    public function test_empty_registry_is_honest(): void
    {
        $response = $this->getJson('/api/inputs/verify?q=DuduAll');

        $response->assertOk()
            ->assertJsonPath('registry_ready', false)
            ->assertJsonPath('registry_count', 0);

        $this->assertStringContainsString('TPRI', $response->json('guidance'));
    }

    public function test_counterfeit_report_flow_pending_until_admin_confirms(): void
    {
        // Farmer reports a suspected fake
        $this->actingAs($this->farmer)->postJson('/api/inputs/report', [
            'product_name' => 'DuduAll 450 EC (bandia)',
            'product_type' => 'insecticide',
            'dealer_name' => 'Duka la Mfano',
            'region' => 'Morogoro',
            'description' => 'Rangi tofauti na ya kawaida, harufu kali isiyo ya kawaida, bei nusu ya soko.',
        ])->assertCreated()
          ->assertJsonPath('alert.status', 'pending');

        // Pending report is NOT publicly visible
        $this->getJson('/api/inputs/alerts?region=Morogoro')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Admin confirms it
        $uuid = CounterfeitAlert::first()->uuid;
        $this->actingAs($this->admin)
            ->postJson("/api/admin/input-alerts/{$uuid}/review", [
                'decision' => 'confirmed',
                'notes' => 'Sampuli imehakikiwa.',
            ])->assertOk();

        // Now it appears publicly, filtered by region
        $this->getJson('/api/inputs/alerts?region=Morogoro')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_name', 'DuduAll 450 EC (bandia)');

        $this->getJson('/api/inputs/alerts?region=Mwanza')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Confirmed alert surfaces in registry lookups too
        $lookup = $this->getJson('/api/inputs/verify?q=DuduAll');
        $this->assertCount(1, $lookup->json('related_alerts'));
        $this->assertStringContainsString('TAHADHARI', $lookup->json('guidance'));
    }

    public function test_alert_review_is_admin_only_and_report_requires_auth(): void
    {
        $this->postJson('/api/inputs/report', [
            'product_name' => 'X', 'region' => 'Dodoma', 'description' => 'test test',
        ])->assertUnauthorized();

        $alert = CounterfeitAlert::create([
            'reporter_id' => $this->farmer->id,
            'product_name' => 'Y',
            'region' => 'Dodoma',
            'description' => 'test',
        ]);

        $this->actingAs($this->farmer)
            ->postJson("/api/admin/input-alerts/{$alert->uuid}/review", ['decision' => 'confirmed'])
            ->assertForbidden();
    }

    public function test_registry_crud_is_admin_only(): void
    {
        $payload = [
            'name' => 'MboleaPlus NPK',
            'type' => 'fertilizer',
            'registration_number' => 'TFRA/TEST/0001',
            'source' => 'TFRA Test List',
        ];

        $this->actingAs($this->farmer)->postJson('/api/admin/inputs', $payload)->assertForbidden();

        $uuid = $this->actingAs($this->admin)->postJson('/api/admin/inputs', $payload)
            ->assertCreated()
            ->json('input.uuid');

        $this->actingAs($this->admin)
            ->putJson("/api/admin/inputs/{$uuid}", ['status' => 'withdrawn'])
            ->assertOk()
            ->assertJsonPath('input.status', 'withdrawn');
    }

    public function test_label_check_without_gemini_key_is_honest_503(): void
    {
        config(['services.gemini.api_key' => null]);

        $file = \Illuminate\Http\Testing\File::image('lebo.jpg');

        $this->actingAs($this->farmer)
            ->postJson('/api/inputs/check-label', ['image' => $file])
            ->assertStatus(503);
    }

    public function test_label_check_cross_references_registry(): void
    {
        $this->seedRegistry();
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'product_name' => 'DuduAll 450 EC',
                            'registration_number' => 'TPRI/TEST/0001',
                            'manufacturer' => 'Test Agro Ltd',
                            'label_warnings' => [],
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);

        $file = \Illuminate\Http\Testing\File::image('lebo.jpg');

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/inputs/check-label', ['image' => $file]);

        $response->assertOk()
            ->assertJsonPath('verdict', 'found_registered')
            ->assertJsonPath('registry_match.name', 'DuduAll 450 EC')
            ->assertJsonPath('extracted.registration_number', 'TPRI/TEST/0001');
    }

    public function test_label_check_flags_unknown_product(): void
    {
        $this->seedRegistry();
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'product_name' => 'SuperGrow Feki',
                            'registration_number' => 'FAKE/123',
                            'manufacturer' => null,
                            'label_warnings' => ['blurry printing'],
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);

        $file = \Illuminate\Http\Testing\File::image('lebo.jpg');

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/inputs/check-label', ['image' => $file]);

        $response->assertOk()->assertJsonPath('verdict', 'not_found');
        $this->assertStringContainsString('DALILI YA HATARI', $response->json('guidance'));
    }

    public function test_checklist_is_available(): void
    {
        $this->getJson('/api/inputs/checklist')
            ->assertOk()
            ->assertJsonStructure(['title', 'items' => [['key', 'text', 'weight']], 'advice']);
    }
}
