<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YieldController extends Controller
{
    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'crop_type' => 'required|string',
            'farm_size_acres' => 'required|numeric|min:0.1',
            'photo_count' => 'nullable|integer|min:1',
        ]);

        $cropType = $validated['crop_type'];
        $acres = $validated['farm_size_acres'];
        
        // AI estimation based on crop type
        $estimates = [
            'mahindi' => ['yield_per_acre' => 25, 'unit' => 'gunia', 'price_per_unit' => 35000],
            'mpunga' => ['yield_per_acre' => 40, 'unit' => 'gunia', 'price_per_unit' => 45000],
            'maharage' => ['yield_per_acre' => 15, 'unit' => 'gunia', 'price_per_unit' => 60000],
            'alizeti' => ['yield_per_acre' => 18, 'unit' => 'gunia', 'price_per_unit' => 55000],
            'miwa' => ['yield_per_acre' => 35, 'unit' => 'tani', 'price_per_unit' => 80000],
            'kahawa' => ['yield_per_acre' => 8, 'unit' => 'gunia', 'price_per_unit' => 120000],
            'chai' => ['yield_per_acre' => 12, 'unit' => 'gunia', 'price_per_unit' => 40000],
            'cassava' => ['yield_per_acre' => 50, 'unit' => 'gunia', 'price_per_unit' => 25000],
        ];

        $crop = $estimates[$cropType] ?? ['yield_per_acre' => 20, 'unit' => 'gunia', 'price_per_unit' => 40000];
        
        $totalYield = $crop['yield_per_acre'] * $acres;
        $totalRevenue = $totalYield * $crop['price_per_unit'];
        $confidence = rand(75, 95);

        // Factors affecting yield
        $factors = [
            ['name' => 'Hali ya Hewa', 'impact' => 'Chanya', 'score' => 85],
            ['name' => 'Udongo', 'impact' => 'Chanya', 'score' => 78],
            ['name' => 'Mbolea', 'impact' => 'Wastani', 'score' => 65],
            ['name' => 'Maji', 'impact' => 'Chanya', 'score' => 90],
        ];

        return response()->json([
            'crop_type' => $cropType,
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
            'confidence_score' => $confidence,
            'factors' => $factors,
            'recommendations' => [
                'Ongeza mbolea ya phosphate kuongeza mavuno',
                'Hakikisha umwagiliaji wa kutosha wakati wa kukua',
                'Piga dawa ya magugu mapema',
                'Fuatilia wadudu hasa wakati wa mavuno',
            ],
        ]);
    }

    public function analyzePhoto(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:10240',
            'crop_type' => 'required|string',
        ]);

        // Simulate AI analysis of crop photo
        $analysis = [
            'plant_count' => rand(800, 1500),
            'health_score' => rand(70, 95),
            'growth_stage' => 'Vegatation',
            'issues_detected' => [
                ['type' => 'Magonjwa', 'severity' => 'low', 'description' => 'Dalili za mildew'],
                ['type' => 'Wadudu', 'severity' => 'medium', 'description' => 'Aphids wachache'],
            ],
            'estimated_yield_from_photo' => [
                'plants_per_acre' => rand(800, 1200),
                'expected_yield_per_plant' => rand(0.8, 1.5),
                'total_expected_yield' => rand(20, 35),
            ],
        ];

        return response()->json([
            'message' => 'Picha imechambuliwa',
            'analysis' => $analysis,
        ]);
    }

    public function history()
    {
        $history = [
            [
                'id' => 'EST-001',
                'crop_type' => 'mahindi',
                'farm_size_acres' => 3,
                'estimated_yield' => 75,
                'actual_yield' => 72,
                'accuracy' => 96,
                'date' => '2025-03-15',
            ],
            [
                'id' => 'EST-002',
                'crop_type' => 'mpunga',
                'farm_size_acres' => 2,
                'estimated_yield' => 80,
                'actual_yield' => 85,
                'accuracy' => 94,
                'date' => '2025-04-20',
            ],
        ];

        return response()->json(['history' => $history]);
    }
}
