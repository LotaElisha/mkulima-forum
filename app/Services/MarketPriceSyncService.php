<?php

namespace App\Services;

use App\Models\MarketPrice;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketPriceSyncService
{
    /**
     * RATIN's single-trend feed returns the same series for every market name,
     * including names that are not markets. It is stored as one national figure.
     *
     * @return array{created: int, updated: int, failed: int, skipped: int, removed: int, enabled: bool}
     */
    public function syncTanzania(): array
    {
        $report = ['created' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0, 'removed' => 0, 'enabled' => true];

        if (! config('services.ratin.sync_enabled')) {
            $report['enabled'] = false;
            Log::info('RATIN market price sync is off (RATIN_SYNC_ENABLED).');

            return $report;
        }

        $admin = $this->recorder();
        $rate = $this->exchangeRate();
        $series = [];

        $summary = $this->fetchSummary('Tanzania');
        if ($summary === null) {
            Log::warning('RATIN summary endpoint returned nothing. National prices come from the trend feed only.');
        }

        foreach ($this->commodities() as $commodity) {
            $trend = $this->fetchTrend($commodity);
            if ($trend === null) {
                $report['skipped']++;

                continue;
            }
            $series[$commodity] = $trend;
        }

        if ($series === []) {
            $report['failed']++;
            Log::warning('RATIN market price sync stored nothing', $report);

            return $report;
        }

        $report['removed'] = $this->removeFabricatedMarkets();

        foreach ($series as $commodity => $trend) {
            $this->storeTrend($commodity, $trend, $rate, $admin, $report);
        }

        Log::info('RATIN market price sync completed', $report);

        return $report;
    }

    protected function fetchSummary(string $country): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withUserAgent('Mozilla/5.0 (MkulimaForum Sync Bot)')
                ->get($this->baseUrl().'/market_prices_summary.php', [
                    'country' => $country,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data['success']) || empty($data['data']['commodityGroups'])) {
                return null;
            }

            return $data['data']['commodityGroups'];
        } catch (\Throwable $e) {
            Log::warning('RATIN summary fetch failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    protected function fetchTrend(string $commodity): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withUserAgent('Mozilla/5.0 (MkulimaForum Sync Bot)')
                ->get($this->baseUrl().'/price_trends.php', [
                    'mode' => 'single',
                    'commodity' => $commodity,
                    'days' => 7,
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
            Log::warning("RATIN trend fetch failed for {$commodity}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * @param  list<array<string, mixed>>  $trend
     * @param  array{created: int, updated: int, failed: int, skipped: int, removed: int, enabled: bool}  $report
     */
    protected function storeTrend(string $commodity, array $trend, ?float $rate, ?User $admin, array &$report): void
    {
        foreach ($trend as $row) {
            $avg = (float) ($row['avg_price'] ?? 0);
            if ($avg <= 0) {
                $report['skipped']++;

                continue;
            }

            $priceType = trim((string) ($row['price_type'] ?? ''));
            $date = (string) ($row['date_label'] ?? '');
            if ($priceType === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $report['skipped']++;

                continue;
            }

            $min = isset($row['min_price']) ? (float) $row['min_price'] : $avg;
            $max = isset($row['max_price']) ? (float) $row['max_price'] : $avg;
            [$avg, $min, $max, $currency] = $this->convert($avg, $min, $max, $rate);

            $this->store(
                commodity: $commodity,
                priceType: $priceType,
                priceDate: $date,
                avg: $avg,
                min: $min,
                max: $max,
                currency: $currency,
                rate: $rate,
                admin: $admin,
                report: $report,
            );
        }
    }

    /**
     * @param  array{created: int, updated: int, failed: int, skipped: int, removed: int, enabled: bool}  $report
     */
    protected function store(
        string $commodity,
        string $priceType,
        string $priceDate,
        float $avg,
        float $min,
        float $max,
        string $currency,
        ?float $rate,
        ?User $admin,
        array &$report,
    ): void {
        $match = [
            'commodity' => $commodity,
            'market' => 'Taifa',
            'region' => 'Tanzania',
            'price_date' => $priceDate,
            'price_type' => $priceType,
        ];

        $values = [
            'min_price' => $min,
            'max_price' => $max,
            'avg_price' => $avg,
            'unit' => 'kg',
            'currency' => $currency,
            'fx_rate' => $currency === 'TZS' ? $rate : null,
            'source' => $this->sourceLabel($priceType, $currency, $rate),
            'recorded_by' => $admin?->id,
        ];

        $existing = MarketPrice::query()->where($match)->first();
        if ($existing) {
            $existing->fill($values)->save();
            $report['updated']++;

            return;
        }

        MarketPrice::query()->create($match + $values);
        $report['created']++;
    }

    protected function sourceLabel(string $priceType, string $currency, ?float $rate): string
    {
        $kind = strtolower($priceType);
        if ($currency === 'TZS' && $rate) {
            return "RATIN/EAGC national {$kind}, 1 USD = {$rate} TZS";
        }

        return "RATIN/EAGC national {$kind}, USD/kg";
    }

    /**
     * Rows written when the sync treated one national series as many towns.
     */
    protected function removeFabricatedMarkets(): int
    {
        return MarketPrice::query()->whereIn('source', [
            'RATIN/EAGC',
            'RATIN/EAGC (per-market)',
        ])->delete();
    }

    /**
     * @return array{0: float, 1: float, 2: float, 3: string}
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

    protected function recorder(): ?User
    {
        try {
            return User::role('admin')->first() ?: User::query()->first();
        } catch (\Throwable) {
            return User::query()->first();
        }
    }

    protected function exchangeRate(): ?float
    {
        $raw = config('services.ratin.usd_to_tzs_rate');
        if (! is_numeric($raw) || (float) $raw <= 0) {
            return null;
        }

        return (float) $raw;
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.ratin.base_url', 'https://ratin.net/ratinapp/api'), '/');
    }

    /**
     * @return list<string>
     */
    protected function commodities(): array
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
