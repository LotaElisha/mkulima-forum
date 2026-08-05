<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\YieldEstimate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YieldController extends Controller
{
    /**
     * Regional average yields & indicative farm-gate prices (TZS) used as a
     * reference table. This is openly labelled as a rough planning estimate —
     * it is NOT an AI prediction of the caller's specific farm.
     */
    protected const REFERENCE = [
        'mahindi' => ['yield_per_acre' => 25, 'unit' => 'gunia', 'price_per_unit' => 35000],
        'mpunga' => ['yield_per_acre' => 40, 'unit' => 'gunia', 'price_per_unit' => 45000],
        'maharage' => ['yield_per_acre' => 15, 'unit' => 'gunia', 'price_per_unit' => 60000],
        'alizeti' => ['yield_per_acre' => 18, 'unit' => 'gunia', 'price_per_unit' => 55000],
        'miwa' => ['yield_per_acre' => 35, 'unit' => 'tani', 'price_per_unit' => 80000],
        'kahawa' => ['yield_per_acre' => 8, 'unit' => 'gunia', 'price_per_unit' => 120000],
        'chai' => ['yield_per_acre' => 12, 'unit' => 'gunia', 'price_per_unit' => 40000],
        'cassava' => ['yield_per_acre' => 50, 'unit' => 'gunia', 'price_per_unit' => 25000],
    ];

    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'crop_type' => 'required|string|in:' . implode(',', array_keys(self::REFERENCE)),
            'farm_size_acres' => 'required|numeric|min:0.1|max:10000',
        ]);

        $crop = self::REFERENCE[$validated['crop_type']];
        $acres = (float) $validated['farm_size_acres'];

        $totalYield = round($crop['yield_per_acre'] * $acres, 2);
        $totalRevenue = round($totalYield * $crop['price_per_unit'], 2);

        $estimate = YieldEstimate::create([
            'user_id' => $request->user()->id,
            'crop_type' => $validated['crop_type'],
            'farm_size_acres' => $acres,
            'yield_per_acre' => $crop['yield_per_acre'],
            'estimated_yield_total' => $totalYield,
            'yield_unit' => $crop['unit'],
            'price_per_unit' => $crop['price_per_unit'],
            'estimated_revenue' => $totalRevenue,
            'method' => 'reference_table',
        ]);

        return response()->json([
            'id' => $estimate->uuid,
            'crop_type' => $validated['crop_type'],
            'farm_size_acres' => $acres,
            'estimated_yield' => [
                'total' => $totalYield,
                'per_acre' => $crop['yield_per_acre'],
                'unit' => $crop['unit'],
            ],
            'estimated_revenue' => [
                'total' => $totalRevenue,
                'per_unit' => $crop['price_per_unit'],
                'currency' => 'TZS',
            ],
            'method' => 'reference_table',
            'disclaimer' => 'Haya ni makadirio ya wastani wa kanda, si utabiri wa shamba lako. '
                . 'Mavuno halisi hutegemea mbegu, udongo, hali ya hewa na utunzaji. '
                . 'Kwa ushauri wa shamba lako, wasiliana na mtaalamu wa kilimo.',
        ]);
    }

    public function analyzePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
            'crop_type' => 'required|string',
        ]);

        // No image-analysis model is wired to this endpoint yet. Answer
        // honestly instead of returning fabricated plant counts (audit
        // 2026-07-12). Use the disease scanner (/scanner/scan) for real
        // AI image analysis.
        return response()->json([
            'message' => 'Uchambuzi wa picha kwa makadirio ya mavuno bado haujawashwa. '
                . 'Tumia "Kagua Ugonjwa" kwa uchambuzi wa magonjwa ya mimea.',
            'available' => false,
        ], 501);
    }

    public function history(Request $request): JsonResponse
    {
        $estimates = YieldEstimate::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'history' => $estimates->map(fn ($e) => [
                'id' => $e->uuid,
                'crop_type' => $e->crop_type,
                'farm_size_acres' => (float) $e->farm_size_acres,
                'estimated_yield' => (float) $e->estimated_yield_total,
                'yield_unit' => $e->yield_unit,
                'estimated_revenue' => (float) $e->estimated_revenue,
                'method' => $e->method,
                'date' => $e->created_at->toDateString(),
            ]),
            'total' => $estimates->total(),
        ]);
    }
}
