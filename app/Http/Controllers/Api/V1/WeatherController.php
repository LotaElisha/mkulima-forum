<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Weather\CurrentWeather;
use App\Http\Api\V1Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function current(Request $request, CurrentWeather $current): JsonResponse
    {
        $validated = $request->validate([
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        return V1Response::ok($current($validated['location'] ?? 'Dar es Salaam'));
    }
}
