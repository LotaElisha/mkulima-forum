<?php

namespace Tests\Feature;

use App\Models\WeatherCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeOpenWeatherSuccess(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'Dodoma',
                'coord' => ['lat' => -6.17, 'lon' => 35.74],
                'main' => ['temp' => 27.4, 'feels_like' => 28.1, 'humidity' => 61, 'pressure' => 1013],
                'wind' => ['speed' => 4.2, 'deg' => 120],
                'weather' => [['description' => 'scattered clouds', 'icon' => '03d']],
                'visibility' => 10000,
                'clouds' => ['all' => 40],
                'sys' => ['sunrise' => 1600000000, 'sunset' => 1600040000],
            ], 200),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response([
                'list' => [
                    [
                        'dt' => now()->addDay()->timestamp,
                        'main' => ['temp_min' => 21.0, 'temp_max' => 29.0, 'humidity' => 65],
                        'wind' => ['speed' => 5.0],
                        'weather' => [['description' => 'light rain', 'icon' => '10d']],
                        'pop' => 0.4,
                    ],
                ],
            ], 200),
        ]);
    }

    public function test_current_returns_real_upstream_data(): void
    {
        $this->fakeOpenWeatherSuccess();

        $response = $this->getJson('/api/weather/current?location=Dodoma');

        $response->assertOk()
            ->assertJsonPath('current.location', 'Dodoma')
            ->assertJsonPath('current.temperature', 27.4)
            ->assertJsonPath('current.humidity', 61)
            ->assertJsonPath('is_stale', false)
            ->assertJsonPath('available', true);
    }

    public function test_upstream_failure_serves_stale_cache_flagged(): void
    {
        WeatherCache::create([
            'location' => 'Mbeya',
            'lat' => -8.9,
            'lon' => 33.45,
            'current_data' => [
                'location' => 'Mbeya',
                'temperature' => 19.2,
                'humidity' => 80,
                'description' => 'overcast clouds',
            ],
            'expires_at' => now()->subHour(), // expired => stale
        ]);

        Http::fake(['api.openweathermap.org/*' => Http::response(null, 500)]);

        $response = $this->getJson('/api/weather/current?location=Mbeya');

        $response->assertOk()
            ->assertJsonPath('current.temperature', 19.2)
            ->assertJsonPath('current.is_stale', true)
            ->assertJsonPath('is_stale', true);
    }

    public function test_no_data_is_honest_not_fabricated(): void
    {
        Http::fake(['api.openweathermap.org/*' => Http::response(null, 500)]);

        $response = $this->getJson('/api/weather/current?location=Kigoma');

        $response->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('current.available', false);

        $this->assertArrayNotHasKey('temperature', $response->json('current'));
    }

    public function test_full_report_bundles_current_forecast_advisory(): void
    {
        $this->fakeOpenWeatherSuccess();

        $response = $this->getJson('/api/weather/report?location=Dodoma');

        $response->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('current.temperature', 27.4)
            ->assertJsonStructure([
                'location', 'available', 'is_stale', 'current',
                'forecast' => [['date', 'temp_min', 'temp_max', 'rain_chance']],
                'advisory' => [['category', 'title', 'message', 'priority']],
            ]);
    }

    public function test_forecast_endpoint_resolves(): void
    {
        $this->fakeOpenWeatherSuccess();

        $this->getJson('/api/weather/forecast?location=Dodoma')
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonCount(1, 'forecast');
    }

    public function test_advisory_endpoint_resolves(): void
    {
        $this->fakeOpenWeatherSuccess();

        $this->getJson('/api/weather/advisory?location=Dodoma')
            ->assertOk()
            ->assertJsonPath('available', true);

        $this->assertNotEmpty($this->getJson('/api/weather/advisory?location=Dodoma')->json('advisory'));
    }
}
