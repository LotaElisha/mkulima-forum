<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    private const OPENWEATHER_API_KEY = 'demo_key'; // Replace with real key
    private const CACHE_MINUTES = 30;

    public function getCurrent(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['nullable', 'numeric'],
            'lon' => ['nullable', 'numeric'],
            'city' => ['nullable', 'string'],
        ]);

        $city = $request->input('city', 'Dar es Salaam');
        $lat = $request->input('lat', -6.7924);
        $lon = $request->input('lon', 39.2083);

        // Check cache
        $cache = WeatherCache::where('location', $city)
            ->where('expires_at', '>', now())
            ->first();

        if ($cache) {
            return response()->json([
                'current' => $cache->current_data,
                'forecast' => $cache->forecast_data,
                'advisory' => $cache->advisory_data,
                'cached' => true,
            ]);
        }

        // Demo weather data (replace with real API call)
        $weatherData = $this->getDemoWeather($city, $lat, $lon);

        // Cache the data
        WeatherCache::updateOrCreate(
            ['location' => $city],
            [
                'lat' => $lat,
                'lon' => $lon,
                'current_data' => $weatherData['current'],
                'forecast_data' => $weatherData['forecast'],
                'advisory_data' => $weatherData['advisory'],
                'expires_at' => now()->addMinutes(self::CACHE_MINUTES),
            ]
        );

        return response()->json($weatherData);
    }

    public function getAdvisory(Request $request): JsonResponse
    {
        $request->validate([
            'crop' => ['required', 'string'],
            'region' => ['required', 'string'],
        ]);

        $crop = $request->input('crop');
        $region = $request->input('region');

        // Demo advisory data
        $advisory = $this->getDemoAdvisory($crop, $region);

        return response()->json([
            'crop' => $crop,
            'region' => $region,
            'advisory' => $advisory,
        ]);
    }

    private function getDemoWeather(string $city, float $lat, float $lon): array
    {
        $conditions = ['Clear', 'Clouds', 'Rain', 'Drizzle', 'Thunderstorm'];
        $current = [
            'temp' => rand(24, 32),
            'feels_like' => rand(26, 34),
            'humidity' => rand(60, 90),
            'pressure' => rand(1010, 1020),
            'wind_speed' => rand(5, 25),
            'wind_direction' => rand(0, 360),
            'visibility' => rand(8000, 10000),
            'uvi' => rand(3, 10),
            'condition' => $conditions[array_rand($conditions)],
            'description' => 'Scattered clouds',
            'icon' => '03d',
            'sunrise' => '06:15',
            'sunset' => '18:45',
        ];

        $forecast = [];
        for ($i = 1; $i <= 5; $i++) {
            $forecast[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'day' => now()->addDays($i)->format('l'),
                'temp_min' => rand(22, 26),
                'temp_max' => rand(28, 34),
                'humidity' => rand(55, 85),
                'condition' => $conditions[array_rand($conditions)],
                'description' => 'Partly cloudy',
                'icon' => '02d',
                'rain_chance' => rand(10, 60),
                'wind_speed' => rand(8, 20),
            ];
        }

        $advisory = [
            'alert_level' => 'low',
            'alerts' => [],
            'farming_tips' => [
                'Wakati mzuri wa kupanda mahindi',
                'Hali ya hewa inafaa kwa kilimo cha mimea ya majani',
                'Hakikisha umeweka mfumo wa umwagiliaji',
            ],
            'recommended_crops' => ['Mahindi', 'Mpunga', 'Mbogamboga'],
            'irrigation_needed' => $current['humidity'] < 70,
        ];

        return [
            'current' => $current,
            'forecast' => $forecast,
            'advisory' => $advisory,
            'location' => [
                'city' => $city,
                'lat' => $lat,
                'lon' => $lon,
            ],
        ];
    }

    private function getDemoAdvisory(string $crop, string $region): array
    {
        $tips = [
            'mahindi' => [
                'Panda wakati wa mvua ya vuli (Oktoba-Desemba)',
                'Tumia mbegu za kuthibitishwa',
                'Weka mbali mita 75 kati ya mstari mmoja na mwingine',
                'Tumia mbolea ya DAP wakati wa kupanda',
            ],
            'mpunga' => [
                'Hakikisha shamba lina maji ya kutosha',
                'Panda katika mstari mmoja kwa mmoja',
                'Tumia mbolea ya NPK mwanzoni',
                'Linda dhidi ya magonjwa ya bakteria',
            ],
            'mbogamboga' => [
                'Panda karibu na chanzo cha maji',
                'Tumia mbolea ya kuku au ngombe',
                'Vuna mapema asubuhi',
                'Hifadhi kwenye joto la chini',
            ],
        ];

        return [
            'general' => $tips[strtolower($crop)] ?? ['Hali ya hewa inafaa kwa kilimo'],
            'pest_alerts' => [],
            'disease_alerts' => [],
            'irrigation_schedule' => [
                'Monday' => 'Morning',
                'Wednesday' => 'Evening',
                'Friday' => 'Morning',
            ],
        ];
    }
}
