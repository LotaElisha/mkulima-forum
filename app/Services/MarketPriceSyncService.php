<?php

namespace App\Services;

use App\Models\MarketPrice;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketPriceSyncService
{
    protected string $baseUrl = 'https://ratin.net/ratinapp/api';

    protected int $perMarketTimeout = 30;

    /**
     * Sync Tanzania market prices from RATIN (EAGC) public API.
     * Fetches country-level summary + per-market trend data for major markets.
     */
    public function syncTanzania(): array
    {
        $report = ['created' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0];

        $admin = (User::role('admin')->first() ?: null)
            ?? (User::first() ?: null);

        $priceDate = now()->toDateString();
        $rate = config('services.ratin.usd_to_tzs_rate', null);

        // 1. Country-level summary (market = Tanzania)
        $summary = $this->fetchSummary('Tanzania');
        if ($summary !== null) {
            foreach ($summary as $item) {
                $this->upsertSummaryRecord($item, $priceDate, $rate, $admin, $report);
            }
        } else {
            $report['failed']++;
        }

        // 2. Per-market trends for major commodities/markets.
        // We limit the matrix to avoid hammering the RATIN server.
        $markets = $this->majorMarkets();
        $commodities = $this->majorCommodityNames();

        foreach ($commodities as $commodity) {
            foreach ($markets as $market) {
                $trend = $this->fetchSingleTrend($commodity, $market, 7);
                if ($trend === null) {
                    continue; // no data or error; not a failure
                }
                $this->upsertTrendRecords($commodity, $market, $trend, $priceDate, $rate, $admin, $report);
            }
        }

        Log::info('RATIN market price sync completed', $report);

        return $report;
    }

    protected function fetchSummary(string $country): ?array
    {
        try {
            $response = Http::timeout(120)
                ->withUserAgent('Mozilla/5.0 (MkulimaForum Sync Bot)')
                ->get("{$this->baseUrl}/market_prices_summary.php", [
                    'country' => $country,
                ]);

            if (! $response->successful()) {
                Log::warning('RATIN summary API returned non-success', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            if (empty($data['success']) || empty($data['data']['commodityGroups'])) {
                Log::warning('RATIN summary API returned unexpected shape', $data);

                return null;
            }

            $items = [];
            foreach ($data['data']['commodityGroups'] as $group) {
                foreach ($group['items'] as $item) {
                    $items[] = $item;
                }
            }

            return $items;
        } catch (\Throwable $e) {
            Log::error('RATIN summary fetch failed: '.$e->getMessage());

            return null;
        }
    }

    protected function upsertSummaryRecord(
        array $item,
        string $priceDate,
        ?float $rate,
        ?User $admin,
        array &$report
    ): void {
        $commodity = $item['commodity'] ?? null;
        $country = $item['country'] ?? 'Tanzania';

        if (! $commodity) {
            $report['skipped']++;

            return;
        }

        foreach (['wholesale', 'retail'] as $priceType) {
            $price = $item['prices'][$priceType] ?? null;
            if (! $price || ! is_numeric($price['countryPrice'] ?? null)) {
                continue;
            }

            [$avg, $min, $max, $currency] = $this->convert(
                (float) $price['countryPrice'],
                (float) ($price['min_price'] ?? $price['countryPrice'] * 0.9),
                (float) ($price['max_price'] ?? $price['countryPrice'] * 1.1),
                $rate
            );

            $this->store(
                commodity: $commodity,
                market: $country,
                region: 'Tanzania',
                priceDate: $priceDate,
                avg: $avg,
                min: $min,
                max: $max,
                unit: 'kg',
                currency: $currency,
                source: 'RATIN/EAGC',
                admin: $admin,
                report: $report
            );
        }
    }

    protected function fetchSingleTrend(string $commodity, string $market, int $days): ?array
    {
        try {
            $response = Http::timeout($this->perMarketTimeout)
                ->withUserAgent('Mozilla/5.0 (MkulimaForum Sync Bot)')
                ->get("{$this->baseUrl}/price_trends.php", [
                    'mode' => 'single',
                    'commodity' => $commodity,
                    'market' => $market,
                    'days' => $days,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (empty($data['success']) || empty($data['data']) || ! is_array($data['data'])) {
                return null;
            }

            return $data['data'];
        } catch (\Throwable $e) {
            Log::warning("RATIN trend fetch failed for {$commodity} @ {$market}: ".$e->getMessage());

            return null;
        }
    }

    protected function upsertTrendRecords(
        string $commodity,
        string $market,
        array $trend,
        string $priceDate,
        ?float $rate,
        ?User $admin,
        array &$report
    ): void {
        // Take the latest entry per price_type (most recent date).
        $latest = [];
        foreach (array_reverse($trend) as $row) {
            $type = $row['price_type'] ?? 'Retail';
            if (! isset($latest[$type])) {
                $latest[$type] = $row;
            }
        }

        foreach ($latest as $row) {
            $avg = (float) ($row['avg_price'] ?? 0);
            $min = (float) ($row['min_price'] ?? $avg * 0.9);
            $max = (float) ($row['max_price'] ?? $avg * 1.1);

            if ($avg <= 0) {
                continue;
            }

            [$avg, $min, $max, $currency] = $this->convert($avg, $min, $max, $rate);

            $this->store(
                commodity: $commodity,
                market: $market,
                region: 'Tanzania',
                priceDate: $priceDate,
                avg: $avg,
                min: $min,
                max: $max,
                unit: 'kg',
                currency: $currency,
                source: 'RATIN/EAGC (per-market)',
                admin: $admin,
                report: $report
            );
        }
    }

    protected function store(
        string $commodity,
        string $market,
        string $region,
        string $priceDate,
        float $avg,
        float $min,
        float $max,
        string $unit,
        string $currency,
        string $source,
        ?User $admin,
        array &$report
    ): void {
        $record = MarketPrice::updateOrCreate(
            [
                'commodity' => $commodity,
                'market' => $market,
                'region' => $region,
                'price_date' => $priceDate,
                'source' => $source,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'min_price' => $min,
                'max_price' => $max,
                'avg_price' => $avg,
                'unit' => $unit,
                'currency' => $currency,
                'recorded_by' => $admin?->id,
            ]
        );

        if ($record->wasRecentlyCreated) {
            $report['created']++;
        } else {
            $report['updated']++;
        }
    }

    /**
     * Convert USD prices to TZS if a rate is configured.
     */
    protected function convert(float $avg, float $min, float $max, ?float $rate): array
    {
        if ($rate && $rate > 0) {
            return [
                round($avg * $rate, 2),
                round($min * $rate, 2),
                round($max * $rate, 2),
                'TZS',
            ];
        }

        return [round($avg, 4), round($min, 4), round($max, 4), 'USD'];
    }

    /**
     * Major markets to query per-commodity. Keep small to be respectful to RATIN.
     */
    protected function majorMarkets(): array
    {
        return [
            'Arusha',
            'Dar es salaam',
            'Dodoma',
            'Iringa',
            'Mwanza',
            'Morogoro',
            'Mbeya', // RATIN uses this spelling?
            'Shinyanga',
            'Tabora',
            'Tanga',
        ];
    }

    /**
     * Base commodity names used by the RATIN single-trend endpoint.
     */
    protected function majorCommodityNames(): array
    {
        return [
            'Maize',
            'Rice',
            'Beans',
            'Soya Beans',
            'Sorghum',
            'Millet',
            'Wheat',
            'Groundnuts Shelled',
            'Pigeon Peas',
            'Green gram',
            'Chick Peas',
            'Lentils',
            'Sesame',
        ];
    }
}
