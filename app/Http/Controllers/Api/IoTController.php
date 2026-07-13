<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * IoT sensor integration is not yet live: there is no device fleet or
 * time-series store behind this API. Endpoints answer honestly instead of
 * returning fabricated sensor readings (audit 2026-07-12).
 */
class IoTController extends Controller
{
    public function sensors(): JsonResponse
    {
        return $this->unavailable();
    }

    public function mySensors(Request $request): JsonResponse
    {
        if (!FeatureFlag::isEnabled('iot_sensors')) {
            return $this->unavailable();
        }

        // Feature enabled but no device registry exists yet — the honest
        // answer is an empty list, never demo sensors.
        return response()->json([
            'sensors' => [],
            'message' => 'Huna sensor zilizosajiliwa bado.',
        ]);
    }

    public function readings(Request $request, string $sensorId): JsonResponse
    {
        if (!FeatureFlag::isEnabled('iot_sensors')) {
            return $this->unavailable();
        }

        return response()->json([
            'message' => 'Sensor haipatikani.',
        ], 404);
    }

    public function storeReading(Request $request): JsonResponse
    {
        if (!FeatureFlag::isEnabled('iot_sensors')) {
            return $this->unavailable();
        }

        return response()->json([
            'message' => 'Usajili wa sensor bado haujawashwa.',
        ], 501);
    }

    protected function unavailable(): JsonResponse
    {
        return response()->json([
            'message' => 'Huduma ya IoT sensors bado haijazinduliwa.',
            'available' => false,
        ], 503);
    }
}
