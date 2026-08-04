<?php

namespace App\Services;

use App\Models\MarketPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketPriceSyncService
{
    protected string $baseUrl = 'https://ratin.net/ratinapp/api';

    /**
     * Sync Tanzania market prices from RATIN (EAGC) public API.
     * RATIN returns USD/kg. We store USD/kg and optionally convert if a rate is set.
     */
    public function syncTanzania(): array
    {
        $report = ['created' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0];

        $admin = (\App\Models\User::role('admin')->first())
            ?? (\App\Models\User::first());

        try {
            $response = Http::timeout(120)
                ->withUserAgent('Mozilla/5.0 (MkulimaForum Sync Bot)')
                ->get("{$this->baseUrl}/market_prices_summary.php", [
                    'country' => 'Tanzania',
                ]);

            if (!$response->successful()) {
                Log::warning('RATIN summary API returned non-success', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $report['failed']++;
                return $report;
            }

            $data = $response->json();

            if (empty($data['success']) || empty($data['data']['commodityGroups'])) {
                Log::warning('RATIN summary API returned unexpected shape', $data);
                $report['failed']++;
                return $report;
            }

            $priceDate = now()->toDateString();
            $rate = config('services.ratin.usd_to_tzs_rate', null);

            foreach ($data['data']['commodityGroups'] as $group) {
                foreach ($group['items'] as $item) {
                    $commodity = $item['commodity'] ?? null;
                    $country = $item['country'] ?? 'Tanzania';

                    if (!$commodity) {
                        $report['skipped']++;
                        continue;
                    }

                    foreach (['wholesale', 'retail'] as $priceType) {
                        $price = $item['prices'][$priceType] ?? null;
                        if (!$price || !is_numeric($price['countryPrice'] ?? null)) {
                            continue;
                        }

                        $usdPrice = (float) $price['countryPrice'];
                        $currency = $rate ? 'TZS' : 'USD';
                        $unit = 'kg';
                        $priceValue = $rate ? round($usdPrice * $rate, 2) : $usdPrice;

                        // Use a modest min/max spread when only an average is available.
                        $minPrice = is_numeric($price['min_price'] ?? null)
                            ? (float) $price['min_price']
                            : $usdPrice * 0.9;
                        $maxPrice = is_numeric($price['max_price'] ?? null)
                            ? (float) $price['max_price']
                            : $usdPrice * 1.1;

                        $minPrice = $rate ? round($minPrice * $rate, 2) : $minPrice;
                        $maxPrice = $rate ? round($maxPrice * $rate, 2) : $maxPrice;

                        $label = ucfirst($priceType);

                        $record = MarketPrice::updateOrCreate(
                            [
                                'commodity' => $commodity,
                                'market' => $country,
                                'region' => 'Tanzania',
                                'price_date' => $priceDate,
                                'source' => 'RATIN/EAGC',
                            ],
                            [
                                'uuid' => (string) Str::uuid(),
                                'min_price' => $minPrice,
                                'max_price' => $maxPrice,
                                'avg_price' => $priceValue,
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
                }
            }
        } catch (\Throwable $e) {
            Log::error('Market price sync failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $report['failed']++;
        }

        Log::info('RATIN market price sync completed', $report);
        return $report;
    }
}
