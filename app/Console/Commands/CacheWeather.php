<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use Illuminate\Console\Command;

class CacheWeather extends Command
{
    protected $signature = 'weather:cache {locations?* : Locations to pre-fetch (default: Dodoma Dar-Es-Salaam Arusha Mwanza Mbeya)}';

    protected $description = 'Pre-fetch and cache current weather and forecast for key locations';

    public function handle(WeatherService $service): int
    {
        $locations = $this->argument('locations') ?: [
            'Dodoma',
            'Dar es Salaam',
            'Arusha',
            'Mwanza',
            'Mbeya',
            'Morogoro',
            'Iringa',
            'Tanga',
        ];

        $fetched = 0;
        $failed = 0;

        foreach ($locations as $location) {
            $this->info("Fetching weather for: {$location}");

            try {
                $current = $service->getCurrentWeather($location);
                $forecast = $service->getForecast($location);

                if (empty($current['available']) || $current['available'] !== false) {
                    $fetched++;
                } else {
                    $this->warn("No current data for {$location}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("Failed for {$location}: ".$e->getMessage());
                $failed++;
            }

            // Be polite to OpenWeather free tier — sleep between requests.
            sleep(1);
        }

        $this->info("Cached weather for {$fetched} locations, {$failed} failures.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
