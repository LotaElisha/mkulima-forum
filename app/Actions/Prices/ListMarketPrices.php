<?php

namespace App\Actions\Prices;

use App\Models\MarketPrice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ListMarketPrices
{
    public function __invoke(Request $request): LengthAwarePaginator
    {
        $validated = $request->validate([
            'commodity' => ['nullable', 'string', 'max:64'],
            'region' => ['nullable', 'string', 'max:64'],
            'market' => ['nullable', 'string', 'max:96'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'unit' => ['nullable', 'string', 'max:32'],
            'latest' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = MarketPrice::query();

        foreach (['commodity', 'region', 'market'] as $column) {
            if (! empty($validated[$column])) {
                $query->where($column, 'ilike', '%'.$this->escapeLike($validated[$column]).'%');
            }
        }

        if (! empty($validated['unit'])) {
            $query->where('unit', $validated['unit']);
        }
        if (! empty($validated['date_from'])) {
            $query->whereDate('price_date', '>=', $validated['date_from']);
        }
        if (! empty($validated['date_to'])) {
            $query->whereDate('price_date', '<=', $validated['date_to']);
        }

        if ($request->boolean('latest')) {
            $query->whereIn('id', function ($sub) {
                $sub->selectRaw('max(id)')
                    ->from('market_prices')
                    ->groupBy('commodity', 'market');
            });
        }

        $prices = $query->orderByDesc('price_date')
            ->orderBy('commodity')
            ->paginate($validated['per_page'] ?? 20);

        $prices->getCollection()->transform(fn (MarketPrice $price) => $this->present($price));

        return $prices;
    }

    /**
     * @return array{commodities: Collection, regions: Collection, markets: Collection}
     */
    public function filters(): array
    {
        return [
            'commodities' => MarketPrice::query()->distinct()->orderBy('commodity')->pluck('commodity')->values(),
            'regions' => MarketPrice::query()->distinct()->orderBy('region')->pluck('region')->values(),
            'markets' => MarketPrice::query()->distinct()->orderBy('market')->pluck('market')->values(),
        ];
    }

    public function present(MarketPrice $price): array
    {
        return [
            'uuid' => $price->uuid,
            'commodity' => $price->commodity,
            'market' => $price->market,
            'region' => $price->region,
            'min_price' => (float) $price->min_price,
            'max_price' => (float) $price->max_price,
            'avg_price' => (float) $price->avg_price,
            'unit' => $price->unit,
            'currency' => $price->currency,
            'price_date' => $price->price_date->toDateString(),
            'is_stale' => $price->price_date->lt(now()->subDays(14)),
            'trend' => $price->trend(),
            'source' => $price->source,
            'updated_at' => $price->updated_at?->toIso8601String(),
        ];
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
