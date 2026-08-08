<?php

namespace Tests\Feature;

use App\Models\DiseaseScan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DiseaseScannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Tenant::firstOrCreate(['id' => 1], ['name' => 'Tanzania', 'country_code' => 'tz']);
        $this->farmer = User::factory()->create(['role' => 'farmer', 'tenant_id' => 1]);
    }

    public function test_disease_scan_unauthenticated_does_not_crash(): void
    {
        config(['services.gemini.api_key' => 'fake-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'disease_name' => 'Maize Lethal Necrosis',
                                        'confidence' => 0.92,
                                        'description' => 'Viral disease affecting maize leaves.',
                                        'treatment' => 'Rotate crops and remove infected plants.',
                                        'affected_areas' => ['leaves', 'stalk'],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('maize_leaf.jpg', 600, 600);

        $response = $this->postJson('/api/scanner/scan', [
            'image' => $file,
            'crop_type' => 'maize',
        ]);

        $response->assertCreated()
            ->assertJsonPath('scan.disease_name', 'Maize Lethal Necrosis')
            ->assertJsonPath('scan.confidence', 0.92);

        $this->assertDatabaseHas('disease_scans', [
            'disease_name' => 'Maize Lethal Necrosis',
            'user_id' => null,
        ]);
    }

    public function test_failed_inference_returns_503_without_500_server_error(): void
    {
        config(['services.gemini.api_key' => 'fake-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(null, 500),
        ]);

        $file = UploadedFile::fake()->image('leaf.jpg', 600, 600);

        $response = $this->postJson('/api/scanner/scan', [
            'image' => $file,
        ]);

        $response->assertStatus(503)
            ->assertJsonPath('error', 'analysis_unavailable');

        $this->assertDatabaseHas('disease_scans', [
            'status' => 'failed',
            'user_id' => null,
        ]);
    }

    public function test_authenticated_user_disease_scan(): void
    {
        config(['services.gemini.api_key' => 'fake-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'disease_name' => 'Healthy',
                                        'confidence' => 0.98,
                                        'description' => 'No signs of disease.',
                                        'treatment' => 'Keep maintaining normal irrigation.',
                                        'affected_areas' => [],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('healthy_leaf.jpg', 600, 600);

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/scanner/scan', [
                'image' => $file,
                'crop_type' => 'tomato',
            ]);

        $response->assertCreated()
            ->assertJsonPath('scan.disease_name', 'Healthy');

        $this->assertDatabaseHas('disease_scans', [
            'disease_name' => 'Healthy',
            'user_id' => $this->farmer->id,
            'tenant_id' => 1,
        ]);
    }
}
