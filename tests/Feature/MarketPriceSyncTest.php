<?php

namespace Tests\Feature;

use App\Models\MarketPrice;
use App\Services\MarketPriceSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.ratin.sync_enabled' => true,
            'services.ratin.usd_to_tzs_rate' => 2600,
        ]);
    }

    public function test_identical_market_names_are_stored_once_as_a_national_figure(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'market_prices_summary.php')) {
                return Http::response('', 200);
            }

            return Http::response([
                'success' => true,
                'data' => [
                    ['date_label' => '2026-10-21', 'price_type' => 'Retail', 'avg_price' => '0.39', 'min_price' => '0.39', 'max_price' => '0.39'],
                    ['date_label' => '2026-10-21', 'price_type' => 'Wholesale', 'avg_price' => '0.35', 'min_price' => '0.35', 'max_price' => '0.35'],
                ],
            ]);
        });

        MarketPrice::create([
            'commodity' => 'Maize', 'market' => 'Arusha', 'region' => 'Tanzania',
            'min_price' => 1, 'max_price' => 1, 'avg_price' => 1,
            'unit' => 'kg', 'price_date' => '2026-10-21', 'source' => 'RATIN/EAGC (per-market)',
        ]);

        $report = app(MarketPriceSyncService::class)->syncTanzania();

        $this->assertSame(0, $report['failed']);
        $this->assertSame(1, $report['removed']);
        $this->assertSame(0, MarketPrice::where('market', 'Arusha')->count());
        $this->assertSame(0, MarketPrice::where('market', 'Dodoma')->count());

        $maize = MarketPrice::where('commodity', 'Maize')->orderBy('price_type')->get();
        $this->assertCount(2, $maize);
        $this->assertSame(['Retail', 'Wholesale'], $maize->pluck('price_type')->all());
        $this->assertTrue($maize->every(fn ($row) => $row->market === 'Taifa' && $row->region === 'Tanzania'));
        $this->assertSame(1014.0, (float) $maize->firstWhere('price_type', 'Retail')->avg_price);
        $this->assertSame(910.0, (float) $maize->firstWhere('price_type', 'Wholesale')->avg_price);
        $this->assertSame(2600.0, (float) $maize->first()->fx_rate);
        $this->assertStringContainsString('1 USD = 2600 TZS', $maize->first()->source);

        $trends = collect(Http::recorded())->filter(
            fn ($pair) => str_contains($pair[0]->url(), 'price_trends.php')
        );
        $this->assertCount(13, $trends);
        $this->assertFalse($trends->contains(
            fn ($pair) => str_contains(urldecode($pair[0]->url()), 'Arusha')
        ));

        $uuids = MarketPrice::query()->orderBy('id')->pluck('uuid')->all();
        $again = app(MarketPriceSyncService::class)->syncTanzania();
        $this->assertSame(0, $again['created']);
        $this->assertGreaterThan(0, $again['updated']);
        $this->assertSame($uuids, MarketPrice::query()->orderBy('id')->pluck('uuid')->all());

        $this->artisan('market-prices:sync')->assertSuccessful();
    }

    public function test_a_missing_rate_keeps_the_usd_figure(): void
    {
        config(['services.ratin.usd_to_tzs_rate' => null]);
        Http::fake([
            'ratin.net/*summary*' => Http::response('', 200),
            'ratin.net/*' => Http::response([
                'success' => true,
                'data' => [
                    ['date_label' => '2026-10-21', 'price_type' => 'Retail', 'avg_price' => '0.39', 'min_price' => '0.32', 'max_price' => '0.54'],
                ],
            ]),
        ]);

        app(MarketPriceSyncService::class)->syncTanzania();

        $row = MarketPrice::where('commodity', 'Maize')->where('price_type', 'Retail')->first();
        $this->assertSame('USD', $row->currency);
        $this->assertNull($row->fx_rate);
        $this->assertEquals(0.39, (float) $row->avg_price);
        $this->assertStringContainsString('USD/kg', $row->source);
    }

    public function test_sync_is_off_unless_the_flag_is_set(): void
    {
        config(['services.ratin.sync_enabled' => false]);
        Http::fake();

        $this->artisan('market-prices:sync')
            ->expectsOutputToContain('RATIN_SYNC_ENABLED')
            ->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(0, MarketPrice::count());
    }
}
