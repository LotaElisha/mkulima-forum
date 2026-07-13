<?php

namespace App\Services;

use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key') ?? env('OPENWEATHER_API_KEY', '');
    }

    public function getCurrentWeather(string $location): array
    {
        $cache = $this->getCachedWeather($location);
        if ($cache) {
            return $cache;
        }

        try {
            $response = Http::get("{$this->baseUrl}/weather", [
                'q' => $location,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = [
                    'location' => $data['name'] ?? $location,
                    'lat' => $data['coord']['lat'] ?? null,
                    'lon' => $data['coord']['lon'] ?? null,
                    'temperature' => round($data['main']['temp'] ?? 0, 1),
                    'feels_like' => round($data['main']['feels_like'] ?? 0, 1),
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'pressure' => $data['main']['pressure'] ?? 0,
                    'wind_speed' => round($data['wind']['speed'] ?? 0, 1),
                    'wind_direction' => $data['wind']['deg'] ?? 0,
                    'description' => $data['weather'][0]['description'] ?? 'Unknown',
                    'icon' => $data['weather'][0]['icon'] ?? '01d',
                    'visibility' => $data['visibility'] ?? 0,
                    'clouds' => $data['clouds']['all'] ?? 0,
                    'sunrise' => $data['sys']['sunrise'] ?? null,
                    'sunset' => $data['sys']['sunset'] ?? null,
                    'timestamp' => now()->toIso8601String(),
                ];

                $this->cacheWeather($location, $result, null, null);
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Weather API error: ' . $e->getMessage());
        }

        // Fall back to last known (stale) reading — never fabricate weather data.
        $stale = $this->getStaleWeather($location);
        if ($stale) {
            $stale['is_stale'] = true;
            return $stale;
        }

        return [
            'location' => $location,
            'available' => false,
            'is_stale' => false,
            'message' => 'Taarifa za hali ya hewa hazipatikani kwa sasa. Jaribu tena baadaye.',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function getForecast(string $location): array
    {
        $cache = WeatherCache::where('location', $location)
            ->where('expires_at', '>', now())
            ->first();

        if ($cache && $cache->forecast_data) {
            return $cache->forecast_data;
        }

        try {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'q' => $location,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'cnt' => 40,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $daily = [];

                foreach ($data['list'] ?? [] as $item) {
                    $date = date('Y-m-d', $item['dt']);
                    if (!isset($daily[$date])) {
                        $daily[$date] = [
                            'date' => $date,
                            'day_name' => date('l', $item['dt']),
                            'temp_min' => $item['main']['temp_min'],
                            'temp_max' => $item['main']['temp_max'],
                            'humidity' => $item['main']['humidity'],
                            'wind_speed' => $item['wind']['speed'],
                            'description' => $item['weather'][0]['description'],
                            'icon' => $item['weather'][0]['icon'],
                            'rain_chance' => ($item['pop'] ?? 0) * 100,
                        ];
                    } else {
                        $daily[$date]['temp_min'] = min($daily[$date]['temp_min'], $item['main']['temp_min']);
                        $daily[$date]['temp_max'] = max($daily[$date]['temp_max'], $item['main']['temp_max']);
                    }
                }

                $forecast = array_values(array_slice($daily, 0, 5));
                $this->cacheWeather($location, null, $forecast, null);
                return $forecast;
            }
        } catch (\Exception $e) {
            Log::error('Forecast API error: ' . $e->getMessage());
        }

        // Fall back to last known (stale) forecast — never fabricate weather data.
        $staleCache = WeatherCache::where('location', $location)->first();
        if ($staleCache && $staleCache->forecast_data) {
            $forecast = $staleCache->forecast_data;
            foreach ($forecast as &$day) {
                $day['is_stale'] = true;
            }
            return $forecast;
        }

        return [];
    }

    public function getFarmingAdvisory(array $weather): array
    {
        $temp = $weather['temperature'] ?? 25;
        $humidity = $weather['humidity'] ?? 50;
        $desc = strtolower($weather['description'] ?? '');
        $advisories = [];

        // Irrigation advisory
        if (str_contains($desc, 'rain') || str_contains($desc, 'storm')) {
            $advisories[] = [
                'category' => 'Umwagiliaji',
                'title' => 'Mvua Inatarajiwa',
                'message' => 'Mvua inatarajiwa leo. Punguza umwagiliaji wa mimea yako ili kuepuka kuharibika kwa mizizi.',
                'priority' => 'high',
                'icon' => 'water_drop',
            ];
        } elseif ($temp > 30 && $humidity < 40) {
            $advisories[] = [
                'category' => 'Umwagiliaji',
                'title' => 'Hali ya Kavu',
                'message' => 'Joto kali na unyevu mdogo. Ongeza umwagiliaji asubuhi na jioni ili kuhifadhi unyevu kwenye udongo.',
                'priority' => 'high',
                'icon' => 'wb_sunny',
            ];
        }

        // Pest advisory
        if ($humidity > 80 && $temp > 25) {
            $advisories[] = [
                'category' => 'Wadudu na Magonjwa',
                'title' => 'Hatari ya Wadudu',
                'message' => 'Unyevu mkubwa na joto la wastani. Nafasi nzuri kwa kuenea kwa wadudu. Angalia mimea yako mara kwa mara.',
                'priority' => 'medium',
                'icon' => 'bug_report',
            ];
        }

        // Harvest advisory
        if (str_contains($desc, 'clear') || str_contains($desc, 'sunny')) {
            $advisories[] = [
                'category' => 'Uvunaji',
                'title' => 'Hali nzuri ya Kukausha',
                'message' => 'Hali ya hewa ni kavu na jua kali. Wakati mzuri wa kukausha mazao kama mahindi na mpunga.',
                'priority' => 'low',
                'icon' => 'agriculture',
            ];
        }

        // General seasonal advice
        $month = (int) date('n');
        if ($month >= 3 && $month <= 5) {
            $advisories[] = [
                'category' => 'Msimu wa Kilimo',
                'title' => 'Msimu wa Vuli',
                'message' => 'Msimu wa mvua za vuli umekaribia. Jiandae kwa kupalilia na kupanda mbegu za msimu huu.',
                'priority' => 'medium',
                'icon' => 'eco',
            ];
        } elseif ($month >= 10 && $month <= 12) {
            $advisories[] = [
                'category' => 'Msimu wa Kilimo',
                'title' => 'Msimu wa Masika',
                'message' => 'Msimu wa mvua za masika umekaribia. Hakikisha mifereji ya kutoa maji iko tayari.',
                'priority' => 'medium',
                'icon' => 'water',
            ];
        }

        if (empty($advisories)) {
            $advisories[] = [
                'category' => 'Ushauri wa Jumla',
                'title' => 'Hali ya Hewa Nzuri',
                'message' => 'Hali ya hewa ni nzuri kwa shughuli za kawaida za shambani. Endelea na ratiba yako ya kilimo.',
                'priority' => 'low',
                'icon' => 'check_circle',
            ];
        }

        return $advisories;
    }

    protected function getCachedWeather(string $location): ?array
    {
        $cache = WeatherCache::where('location', $location)
            ->where('expires_at', '>', now())
            ->first();

        if ($cache && $cache->current_data) {
            return $cache->current_data;
        }

        return null;
    }

    protected function cacheWeather(string $location, ?array $current, ?array $forecast, ?array $advisory): void
    {
        // A cache-write failure must never break serving fresh upstream data.
        try {
            $cache = WeatherCache::firstOrNew(['location' => $location]);
            if ($current) $cache->current_data = $current;
            if ($forecast) $cache->forecast_data = $forecast;
            if ($advisory) $cache->advisory_data = $advisory;
            $cache->expires_at = now()->addMinutes(30);
            $cache->save();
        } catch (\Exception $e) {
            Log::warning('Weather cache write failed: ' . $e->getMessage());
        }
    }

    /**
     * Last known reading regardless of expiry — used as a clearly-flagged stale fallback.
     */
    protected function getStaleWeather(string $location): ?array
    {
        $cache = WeatherCache::where('location', $location)->first();

        return ($cache && $cache->current_data) ? $cache->current_data : null;
    }
}
