<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketPriceController extends Controller
{
    /**
     * Public price board with filters. Always exposes price_date so stale
     * data is visible; `is_stale` flags entries older than 14 days.
     */
    public function index(Request $request): JsonResponse
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

        if (!empty($validated['commodity'])) {
            $query->where('commodity', 'like', '%' . $validated['commodity'] . '%');
        }
        if (!empty($validated['region'])) {
            $query->where('region', 'like', '%' . $validated['region'] . '%');
        }
        if (!empty($validated['market'])) {
            $query->where('market', 'like', '%' . $validated['market'] . '%');
        }
        if (!empty($validated['unit'])) {
            $query->where('unit', $validated['unit']);
        }
        if (!empty($validated['date_from'])) {
            $query->whereDate('price_date', '>=', $validated['date_from']);
        }
        if (!empty($validated['date_to'])) {
            $query->whereDate('price_date', '<=', $validated['date_to']);
        }

        // latest=1: only the most recent record per commodity+market
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

        $prices->getCollection()->transform(fn ($p) => $this->present($p));

        return response()->json($prices);
    }

    /**
     * Distinct commodities / regions / markets for filter dropdowns.
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'commodities' => MarketPrice::distinct()->orderBy('commodity')->pluck('commodity'),
            'regions' => MarketPrice::distinct()->orderBy('region')->pluck('region'),
            'markets' => MarketPrice::distinct()->orderBy('market')->pluck('market'),
        ]);
    }

    // ---------------- Admin ----------------

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commodity' => ['required', 'string', 'max:64'],
            'market' => ['required', 'string', 'max:96'],
            'region' => ['required', 'string', 'max:64'],
            'min_price' => ['required', 'numeric', 'min:0'],
            'max_price' => ['required', 'numeric', 'min:0', 'gte:min_price'],
            'avg_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:32'],
            'currency' => ['nullable', 'string', 'max:8'],
            'price_date' => ['required', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:128'],
        ]);

        $validated['avg_price'] ??= round(($validated['min_price'] + $validated['max_price']) / 2, 2);
        $validated['currency'] ??= 'TZS';
        $validated['recorded_by'] = $request->user()->id;

        $price = MarketPrice::create($validated);

        return response()->json([
            'message' => 'Bei imehifadhiwa.',
            'price' => $this->present($price),
        ], 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $price = MarketPrice::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'commodity' => ['sometimes', 'string', 'max:64'],
            'market' => ['sometimes', 'string', 'max:96'],
            'region' => ['sometimes', 'string', 'max:64'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'avg_price' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:32'],
            'price_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'source' => ['sometimes', 'nullable', 'string', 'max:128'],
        ]);

        $price->update($validated);

        return response()->json([
            'message' => 'Bei imesasishwa.',
            'price' => $this->present($price->fresh()),
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        MarketPrice::where('uuid', $uuid)->firstOrFail()->delete();

        return response()->json(['message' => 'Bei imefutwa.']);
    }

    protected function present(MarketPrice $price): array
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
            'updated_at' => $price->updated_at->toIso8601String(),
        ];
    }
}
