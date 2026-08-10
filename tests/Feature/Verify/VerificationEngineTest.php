<?php

namespace Tests\Feature\Verify;

use App\Models\ProductCategory;
use App\Models\RegulatedProduct;
use App\Models\RegulatoryAuthority;
use App\Services\Verify\VerificationEngine;
use Database\Seeders\RegulatoryAuthoritySeeder;
use Database\Seeders\SpineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected VerificationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpineSeeder::class);
        $this->seed(RegulatoryAuthoritySeeder::class);
        $this->engine = app(VerificationEngine::class);
    }

    public function test_verify_known_product_returns_verified_status_and_provenance(): void
    {
        $tphpa = RegulatoryAuthority::where('acronym', 'TPHPA')->first();
        $category = ProductCategory::where('code', 'PESTICIDE')->first();

        $product = RegulatedProduct::create([
            'registration_number' => 'TPHPA/2026/001',
            'trade_name' => 'KilimoSafisha 500EC',
            'active_ingredient' => 'Chlorpyrifos 500g/L',
            'authority_id' => $tphpa->id,
            'category_id' => $category->id,
            'registration_status' => 'REGISTERED',
            'provenance' => 'REGULATORY',
            'confidence' => 100,
        ]);

        $result = $this->engine->verify('TPHPA/2026/001');

        $this->assertEquals('REGISTERED_SOURCE_CONFIRMED', $result['status']);
        $this->assertEquals('REGULATORY', $result['provenance']);
        $this->assertEquals(100, $result['confidence']);
        $this->assertEquals('KilimoSafisha 500EC', $result['product']['trade_name']);
        $this->assertNotEmpty($result['reasons']);
        $this->assertNotEmpty($result['recommended_action']['sw']);
    }

    public function test_verify_unknown_product_returns_unverified(): void
    {
        $result = $this->engine->verify('UNKNOWN_REG_999');

        $this->assertEquals('UNVERIFIED', $result['status']);
        $this->assertNull($result['product']);
        $this->assertNotEmpty($result['reasons']);
    }
}
