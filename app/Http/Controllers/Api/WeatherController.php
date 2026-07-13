<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Weather API backed by WeatherService (OpenWeather + stale-cache fallback).
 * Never fabricates readings: when upstream is down the last cached reading is
 * returned flagged `is_stale`, otherwise `available: false` with a message.
 */
class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weather)
    {
    }

    public function current(Request $request): JsonResponse
    {
        $current = $this->weather->getCurrentWeather($this->location($request));

        return response()->json([
            'current' => $current,
            'available' => $current['available'] ?? true,
            'is_stale' => $current['is_stale'] ?? false,
        ]);
    }

    public function forecast(Request $request): JsonResponse
    {
        $forecast = $this->weather->getForecast($this->location($request));

        return response()->json([
            'forecast' => $forecast,
            'available' => !empty($forecast),
        ]);
    }

    public function advisory(Request $request): JsonResponse
    {
        $current = $this->weather->getCurrentWeather($this->location($request));

        if (($current['available'] ?? true) === false) {
            return response()->json([
                'advisory' => [],
                'available' => false,
                'message' => $current['message'] ?? 'Taarifa za hali ya hewa hazipatikani kwa sasa.',
            ], 200);
        }

        return response()->json([
            'advisory' => $this->weather->getFarmingAdvisory($current),
            'based_on' => $current,
            'available' => true,
            'is_stale' => $current['is_stale'] ?? false,
        ]);
    }

    public function fullReport(Request $request): JsonResponse
    {
        $location = $this->location($request);
        $current = $this->weather->getCurrentWeather($location);
        $available = ($current['available'] ?? true) !== false;

        return response()->json([
            'location' => $current['location'] ?? $location,
            'available' => $available,
            'is_stale' => $current['is_stale'] ?? false,
            'current' => $available ? $current : null,
            'forecast' => $available ? $this->weather->getForecast($location) : [],
            'advisory' => $available ? $this->weather->getFarmingAdvisory($current) : [],
            'message' => $available ? null : ($current['message'] ?? 'Taarifa za hali ya hewa hazipatikani kwa sasa.'),
        ]);
    }

    protected function location(Request $request): string
    {
        $request->validate([
            'location' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        return $request->input('location')
            ?? $request->input('city')
            ?? 'Dar es Salaam';
    }
}
