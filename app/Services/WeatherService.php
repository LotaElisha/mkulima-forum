<?php

namespace App\Services;

use App\Models\WeatherCache;
use App\Services\AI\AIService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected ?string $openWeatherApiKey;

    protected bool $useOpenMeteo;

    protected string $openWeatherUrl = 'https://api.openweathermap.org/data/2.5';

    protected string $openMeteoUrl = 'https://api.open-meteo.com/v1';

    protected string $geocodingUrl = 'https://geocoding-api.open-meteo.com/v1';

    public function __construct()
    {
        $this->openWeatherApiKey = config('services.openweather.api_key') ?? env('OPENWEATHER_API_KEY', null);
        $this->useOpenMeteo = config('services.weather.use_open_meteo', true);
    }

    public function getCurrentWeather(string $location): array
    {
        $cache = $this->getCachedWeather($location);
        if ($cache) {
            return $cache;
        }

        try {
            if ($this->openWeatherApiKey && ! $this->useOpenMeteo) {
                $result = $this->fetchOpenWeatherCurrent($location);
            } else {
                $result = $this->fetchOpenMeteoCurrent($location);
            }

            if ($result) {
                $this->cacheWeather($location, $result, null, null);

                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Weather API error: '.$e->getMessage());
        }

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
            if ($this->openWeatherApiKey && ! $this->useOpenMeteo) {
                $forecast = $this->fetchOpenWeatherForecast($location);
            } else {
                $forecast = $this->fetchOpenMeteoForecast($location);
            }

            if ($forecast !== null) {
                $this->cacheWeather($location, null, $forecast, null);

                return $forecast;
            }
        } catch (\Exception $e) {
            Log::error('Forecast API error: '.$e->getMessage());
        }

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

    protected function fetchOpenMeteoCurrent(string $location): ?array
    {
        $coords = $this->geocode($location);
        if (! $coords) {
            Log::warning("Geocoding failed for {$location}");

            return null;
        }

        $response = Http::timeout(30)->get("{$this->openMeteoUrl}/forecast", [
            'latitude' => $coords['lat'],
            'longitude' => $coords['lon'],
            'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,wind_direction_10m,precipitation,cloud_cover,surface_pressure',
            'timezone' => 'auto',
            'forecast_days' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $current = $data['current'] ?? null;
        if (! $current) {
            return null;
        }

        return [
            'location' => $coords['name'],
            'lat' => $coords['lat'],
            'lon' => $coords['lon'],
            'temperature' => round($current['temperature_2m'] ?? 0, 1),
            'feels_like' => round($current['temperature_2m'] ?? 0, 1),
            'humidity' => $current['relative_humidity_2m'] ?? 0,
            'pressure' => $current['surface_pressure'] ?? 0,
            'wind_speed' => round($current['wind_speed_10m'] ?? 0, 1),
            'wind_direction' => $current['wind_direction_10m'] ?? 0,
            'description' => $this->wmoDescription($current['weather_code'] ?? 0),
            'icon' => $this->wmoIcon($current['weather_code'] ?? 0),
            'visibility' => 10000,
            'clouds' => $current['cloud_cover'] ?? 0,
            'sunrise' => null,
            'sunset' => null,
            'precipitation' => $current['precipitation'] ?? 0,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function fetchOpenMeteoForecast(string $location): ?array
    {
        $coords = $this->geocode($location);
        if (! $coords) {
            return null;
        }

        $response = Http::timeout(30)->get("{$this->openMeteoUrl}/forecast", [
            'latitude' => $coords['lat'],
            'longitude' => $coords['lon'],
            'daily' => 'temperature_2m_max,temperature_2m_min,relative_humidity_2m_mean,weather_code,wind_speed_10m_max,precipitation_sum,precipitation_probability_max',
            'timezone' => 'auto',
            'forecast_days' => 6,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $daily = $data['daily'] ?? null;
        if (! $daily) {
            return null;
        }

        $forecast = [];
        $days = count($daily['time'] ?? []);
        for ($i = 0; $i < $days; $i++) {
            $forecast[] = [
                'date' => $daily['time'][$i] ?? null,
                'day_name' => date('l', strtotime($daily['time'][$i] ?? 'now')),
                'temp_min' => $daily['temperature_2m_min'][$i] ?? null,
                'temp_max' => $daily['temperature_2m_max'][$i] ?? null,
                'humidity' => $daily['relative_humidity_2m_mean'][$i] ?? null,
                'wind_speed' => $daily['wind_speed_10m_max'][$i] ?? null,
                'description' => $this->wmoDescription($daily['weather_code'][$i] ?? 0),
                'icon' => $this->wmoIcon($daily['weather_code'][$i] ?? 0),
                'rain_chance' => $daily['precipitation_probability_max'][$i] ?? 0,
                'precipitation' => $daily['precipitation_sum'][$i] ?? 0,
            ];
        }

        return array_values(array_slice($forecast, 0, 5));
    }

    protected function geocode(string $location): ?array
    {
        $response = Http::timeout(30)->get("{$this->geocodingUrl}/search", [
            'name' => $location,
            'count' => 1,
            'language' => 'en',
            'format' => 'json',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $results = $response->json('results');
        if (empty($results) || ! is_array($results)) {
            return null;
        }

        $first = $results[0];

        return [
            'name' => $first['name'] ?? $location,
            'lat' => $first['latitude'] ?? null,
            'lon' => $first['longitude'] ?? null,
            'country' => $first['country'] ?? null,
        ];
    }

    protected function fetchOpenWeatherCurrent(string $location): ?array
    {
        $response = Http::get("{$this->openWeatherUrl}/weather", [
            'q' => $location,
            'appid' => $this->openWeatherApiKey,
            'units' => 'metric',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
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
    }

    protected function fetchOpenWeatherForecast(string $location): ?array
    {
        $response = Http::get("{$this->openWeatherUrl}/forecast", [
            'q' => $location,
            'appid' => $this->openWeatherApiKey,
            'units' => 'metric',
            'cnt' => 40,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        $daily = [];

        foreach ($data['list'] ?? [] as $item) {
            $date = date('Y-m-d', $item['dt']);
            if (! isset($daily[$date])) {
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

        return array_values(array_slice($daily, 0, 5));
    }

    protected function wmoDescription(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            $code <= 3 => 'Partly cloudy',
            $code <= 48 => 'Foggy',
            $code <= 57 => 'Drizzle',
            $code <= 67 => 'Rain',
            $code <= 77 => 'Snow',
            $code <= 82 => 'Showers',
            $code <= 86 => 'Snow showers',
            $code === 95 => 'Thunderstorm',
            $code <= 99 => 'Thunderstorm with hail',
            default => 'Unknown',
        };
    }

    protected function wmoIcon(int $code): string
    {
        return match (true) {
            $code === 0 => '01d',
            $code <= 3 => '03d',
            $code <= 48 => '50d',
            $code <= 67 => '10d',
            $code <= 77 => '13d',
            $code <= 86 => '09d',
            $code <= 99 => '11d',
            default => '01d',
        };
    }

    public function getFarmingAdvisory(array $weather): array
    {
        $temp = $weather['temperature'] ?? 25;
        $humidity = $weather['humidity'] ?? 50;
        $desc = strtolower($weather['description'] ?? '');
        $location = $weather['location'] ?? 'Tanzania';

        try {
            $aiService = app(AIService::class);
            $prompt = "Provide a JSON array of localized agricultural advisories for a farmer in {$location} with current weather {$temp}°C, humidity {$humidity}%, {$desc}. Search live regional micro-climate forecasts. Each item must have: category (string), title (string), message (string in Swahili), priority ('high'|'medium'|'low'), icon ('water_drop'|'wb_sunny'|'bug_report'|'agriculture'). Return ONLY valid JSON array.";

            $aiResponse = $aiService->generateText(
                'weather_crop_advisory',
                [['role' => 'user', 'content' => $prompt]],
                [
                    'model' => 'gemini-3-pro-preview',
                    'enable_grounding' => true,
                    'temperature' => 0.3,
                ]
            );

            if (! empty($aiResponse->text)) {
                $cleanJson = preg_replace('/```json\s*|\s*```/', '', trim($aiResponse->text));
                $parsed = json_decode($cleanJson, true);
                if (is_array($parsed) && count($parsed) > 0) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::info('Gemini 3 Pro Weather Advisory fallback: '.$e->getMessage());
        }

        $advisories = [];

        if (str_contains($desc, 'rain') || str_contains($desc, 'storm') || str_contains($desc, 'shower')) {
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

        if ($humidity > 80 && $temp > 25) {
            $advisories[] = [
                'category' => 'Wadudu na Magonjwa',
                'title' => 'Hatari ya Wadudu',
                'message' => 'Unyevu mkubwa na joto la wastani. Nafasi nzuri kwa kuenea kwa wadudu. Angalia mimea yako mara kwa mara.',
                'priority' => 'medium',
                'icon' => 'bug_report',
            ];
        }

        if (str_contains($desc, 'clear') || str_contains($desc, 'sunny')) {
            $advisories[] = [
                'category' => 'Uvunaji',
                'title' => 'Hali nzuri ya Kukausha',
                'message' => 'Hali ya hewa ni kavu na jua kali. Wakati mzuri wa kukausha mazao kama mahindi na mpunga.',
                'priority' => 'low',
                'icon' => 'agriculture',
            ];
        }

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
        try {
            $cache = WeatherCache::firstOrNew(['location' => $location]);
            if ($current) {
                $cache->current_data = $current;
            }
            if ($forecast) {
                $cache->forecast_data = $forecast;
            }
            if ($advisory) {
                $cache->advisory_data = $advisory;
            }
            $cache->expires_at = now()->addMinutes(30);
            $cache->save();
        } catch (\Exception $e) {
            Log::warning('Weather cache write failed: '.$e->getMessage());
        }
    }

    protected function getStaleWeather(string $location): ?array
    {
        $cache = WeatherCache::where('location', $location)->first();

        return ($cache && $cache->current_data) ? $cache->current_data : null;
    }
}
