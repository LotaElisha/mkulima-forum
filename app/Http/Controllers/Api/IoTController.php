<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IoTController extends Controller
{
    public function sensors()
    {
        $sensors = [
            [
                'id' => 'soil-moisture',
                'name' => 'Soil Moisture Sensor',
                'type' => 'soil',
                'description' => 'Kipima unyevu wa udongo',
                'price' => 45000,
                'icon' => 'water',
            ],
            [
                'id' => 'weather-station',
                'name' => 'Weather Station',
                'type' => 'weather',
                'description' => 'Kipima mvua, joto, upepo',
                'price' => 120000,
                'icon' => 'cloud-sun',
            ],
            [
                'id' => 'ph-sensor',
                'name' => 'Soil pH Sensor',
                'type' => 'soil',
                'description' => 'Kipima acidity ya udongo',
                'price' => 65000,
                'icon' => 'flask',
            ],
            [
                'id' => 'nutrient-sensor',
                'name' => 'Nutrient Sensor',
                'type' => 'soil',
                'description' => 'Kipima nitrogen, phosphorus, potassium',
                'price' => 95000,
                'icon' => 'leaf',
            ],
        ];

        return response()->json(['sensors' => $sensors]);
    }

    public function mySensors()
    {
        $user = Auth::user();
        
        $sensors = [
            [
                'id' => 'SNS-001',
                'name' => 'Soil Moisture - Shamba 1',
                'type' => 'soil-moisture',
                'location' => 'Shamba la Arusha',
                'status' => 'online',
                'battery' => 78,
                'last_reading' => [
                    'moisture' => 45,
                    'temperature' => 28.5,
                    'timestamp' => now()->subMinutes(5)->toIso8601String(),
                ],
                'readings' => [
                    ['time' => '06:00', 'moisture' => 42, 'temp' => 24],
                    ['time' => '09:00', 'moisture' => 38, 'temp' => 27],
                    ['time' => '12:00', 'moisture' => 35, 'temp' => 31],
                    ['time' => '15:00', 'moisture' => 40, 'temp' => 29],
                    ['time' => '18:00', 'moisture' => 45, 'temp' => 26],
                ],
            ],
            [
                'id' => 'SNS-002',
                'name' => 'Weather Station - Shamba 2',
                'type' => 'weather-station',
                'location' => 'Shamba la Dodoma',
                'status' => 'online',
                'battery' => 92,
                'last_reading' => [
                    'temperature' => 30.2,
                    'humidity' => 65,
                    'rainfall' => 0,
                    'wind_speed' => 12,
                    'timestamp' => now()->subMinutes(2)->toIso8601String(),
                ],
                'readings' => [
                    ['time' => '06:00', 'temp' => 22, 'humidity' => 80],
                    ['time' => '09:00', 'temp' => 26, 'humidity' => 70],
                    ['time' => '12:00', 'temp' => 32, 'humidity' => 55],
                    ['time' => '15:00', 'temp' => 30, 'humidity' => 60],
                    ['time' => '18:00', 'temp' => 25, 'humidity' => 75],
                ],
            ],
        ];

        return response()->json(['sensors' => $sensors]);
    }

    public function readings($sensorId)
    {
        $readings = [
            'sensor_id' => $sensorId,
            'data' => [
                ['date' => '2025-06-01', 'moisture' => 45, 'temp' => 28],
                ['date' => '2025-06-02', 'moisture' => 42, 'temp' => 29],
                ['date' => '2025-06-03', 'moisture' => 38, 'temp' => 30],
                ['date' => '2025-06-04', 'moisture' => 40, 'temp' => 28],
                ['date' => '2025-06-05', 'moisture' => 44, 'temp' => 27],
                ['date' => '2025-06-06', 'moisture' => 46, 'temp' => 26],
            ],
        ];

        return response()->json($readings);
    }

    public function storeReading(Request $request)
    {
        $validated = $request->validate([
            'sensor_id' => 'required|string',
            'moisture' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            'nutrients' => 'nullable|array',
        ]);

        // In production, store in time-series database
        return response()->json([
            'message' => 'Soma limehifadhiwa',
            'reading' => $validated,
        ]);
    }
}
