<?php

namespace App\Actions\Weather;

use App\Services\WeatherService;

class CurrentWeather
{
    public function __construct(private readonly WeatherService $weather) {}

    /**
     * @return array{location: string, available: bool, is_stale: bool, current: ?array, message: ?string}
     */
    public function __invoke(string $location): array
    {
        $current = $this->weather->getCurrentWeather($location);
        $available = ($current['available'] ?? true) !== false;

        return [
            'location' => $current['location'] ?? $location,
            'available' => $available,
            'is_stale' => (bool) ($current['is_stale'] ?? false),
            'current' => $available ? $current : null,
            'message' => $available ? null : ($current['message'] ?? 'Taarifa za hali ya hewa hazipatikani kwa sasa.'),
        ];
    }
}
