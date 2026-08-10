<?php

namespace Tests\Feature\Spine;

use App\Services\Spine\OfflineBundleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineBundleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SpineSeeder::class);
        $this->seed(\Database\Seeders\RegulatoryAuthoritySeeder::class);
        $this->seed(\Database\Seeders\CommunityChannelSeeder::class);
    }

    public function test_offline_bundle_service_generates_valid_signature_and_envelope(): void
    {
        $service = app(OfflineBundleService::class);
        $bundle = $service->generateBundle();

        $this->assertArrayHasKey('bundle_version', $bundle);
        $this->assertArrayHasKey('signature', $bundle);
        $this->assertArrayHasKey('payload', $bundle);
        $this->assertIsArray($bundle['payload']['registered_products']);
        $this->assertIsArray($bundle['payload']['community_channels']);
    }

    public function test_offline_bundle_api_endpoint_returns_signed_bundle(): void
    {
        $response = $this->getJson('/api/v1/sync/bundle');

        $response->assertStatus(200)
            ->assertHeader('ETag')
            ->assertJsonStructure([
                'status',
                'data' => ['bundle_version', 'generated_at', 'signature', 'payload']
            ]);
    }
}
