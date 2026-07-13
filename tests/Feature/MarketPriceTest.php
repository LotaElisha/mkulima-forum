<?php

namespace Tests\Feature;

use App\Models\MarketPrice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketPriceTest extends TestCase
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

    public function test_admin_can_record_price_and_public_can_read_it(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/admin/market-prices', [
                'commodity' => 'mahindi',
                'market' => 'Kariakoo',
                'region' => 'Dar es Salaam',
                'min_price' => 40000,
                'max_price' => 50000,
                'unit' => 'gunia la kg 100',
                'price_date' => now()->toDateString(),
                'source' => 'Wizara ya Kilimo',
            ])
            ->assertCreated()
            ->assertJsonPath('price.avg_price', fn ($v) => (float) $v === 45000.0);

        $this->getJson('/api/market-prices?commodity=mahindi')
            ->assertOk()
            ->assertJsonPath('data.0.commodity', 'mahindi')
            ->assertJsonPath('data.0.is_stale', false)
            ->assertJsonPath('data.0.source', 'Wizara ya Kilimo');
    }

    public function test_farmer_cannot_record_prices(): void
    {
        $this->actingAs($this->farmer)
            ->postJson('/api/admin/market-prices', [
                'commodity' => 'mahindi',
                'market' => 'Kariakoo',
                'region' => 'Dar es Salaam',
                'min_price' => 1,
                'max_price' => 2,
                'unit' => 'kg',
                'price_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_filters_and_trend_and_staleness(): void
    {
        MarketPrice::create([
            'commodity' => 'mahindi', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam',
            'min_price' => 40000, 'max_price' => 44000, 'avg_price' => 42000,
            'unit' => 'gunia', 'price_date' => now()->subDays(30)->toDateString(),
        ]);
        MarketPrice::create([
            'commodity' => 'mahindi', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam',
            'min_price' => 43000, 'max_price' => 47000, 'avg_price' => 45000,
            'unit' => 'gunia', 'price_date' => now()->toDateString(),
        ]);
        MarketPrice::create([
            'commodity' => 'mpunga', 'market' => 'Soko Kuu', 'region' => 'Mwanza',
            'min_price' => 60000, 'max_price' => 70000, 'avg_price' => 65000,
            'unit' => 'gunia', 'price_date' => now()->subDays(20)->toDateString(),
        ]);

        // Region filter
        $this->getJson('/api/market-prices?region=Mwanza')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.commodity', 'mpunga')
            ->assertJsonPath('data.0.is_stale', true); // 20 days old must be flagged

        // Latest-only per commodity+market with upward trend
        $response = $this->getJson('/api/market-prices?commodity=mahindi&latest=1');
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('up', $response->json('data.0.trend'));
        $this->assertEquals(45000, $response->json('data.0.avg_price'));

        // Filter values endpoint
        $this->getJson('/api/market-prices/filters')
            ->assertOk()
            ->assertJsonFragment(['mahindi'])
            ->assertJsonFragment(['Mwanza']);
    }

    public function test_sms_price_query_uses_real_data(): void
    {
        MarketPrice::create([
            'commodity' => 'mahindi', 'market' => 'Kariakoo', 'region' => 'Dar es Salaam',
            'min_price' => 43000, 'max_price' => 47000, 'avg_price' => 45000,
            'unit' => 'gunia', 'price_date' => now()->toDateString(),
        ]);

        $this->postJson('/api/sms/receive', ['from' => '255700000001', 'text' => 'BEI mahindi'])
            ->assertOk()
            ->assertJsonFragment(['message' => "Bei za mahindi:\nKariakoo: TZS 45,000/gunia (" . now()->format('d/m') . ")\nApp: https://mkulima.hudumapro.com"]);

        // No data → honest apology, never invented prices
        $response = $this->postJson('/api/sms/receive', ['from' => '255700000001', 'text' => 'BEI vanilla']);
        $response->assertOk();
        $this->assertStringContainsString('hatuna bei', $response->json('message'));
    }
}
