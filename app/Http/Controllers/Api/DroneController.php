<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DroneController extends Controller
{
    public function services()
    {
        $services = [
            [
                'id' => 'spraying',
                'name' => 'Drone Spraying',
                'description' => 'Puliza dawa za wadudu au mbolea kutoka juu kwa usahihi mkubwa',
                'price_per_acre' => 15000,
                'duration' => '30 min / acre',
                'icon' => 'spray',
            ],
            [
                'id' => 'mapping',
                'name' => 'Aerial Mapping',
                'description' => 'Piga picha za shamba lako kutoka juu kwa mahesabu na mpango',
                'price_per_acre' => 8000,
                'duration' => '20 min / acre',
                'icon' => 'map',
            ],
            [
                'id' => 'monitoring',
                'name' => 'Crop Monitoring',
                'description' => 'Fuatilia afya ya mimea yako kila wiki kwa picha za infrared',
                'price_per_acre' => 5000,
                'duration' => '15 min / acre',
                'icon' => 'monitor',
            ],
            [
                'id' => 'seeding',
                'name' => 'Drone Seeding',
                'description' => 'Panda mbegu kwa usahiki mkubwa kutoka juu',
                'price_per_acre' => 12000,
                'duration' => '25 min / acre',
                'icon' => 'seed',
            ],
        ];

        return response()->json(['services' => $services]);
    }

    public function book(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|string',
            'farm_location' => 'required|string',
            'farm_size_acres' => 'required|numeric|min:0.5',
            'preferred_date' => 'required|date|after:today',
            'contact_phone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $totalCost = $this->calculateCost($validated['service_id'], $validated['farm_size_acres']);

        $booking = [
            'id' => 'DRN-' . time(),
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'farm_location' => $validated['farm_location'],
            'farm_size_acres' => $validated['farm_size_acres'],
            'preferred_date' => $validated['preferred_date'],
            'contact_phone' => $validated['contact_phone'],
            'notes' => $validated['notes'],
            'total_cost' => $totalCost,
            'status' => 'pending',
            'created_at' => now()->toIso8601String(),
        ];

        // Store in cache for demo
        \Cache::put('drone_booking:' . $booking['id'], $booking, 86400);

        return response()->json([
            'message' => 'Ombi la drone limepokelewa',
            'booking' => $booking,
        ]);
    }

    public function myBookings()
    {
        $user = Auth::user();
        // Return demo bookings
        $bookings = [
            [
                'id' => 'DRN-123456',
                'service_name' => 'Drone Spraying',
                'farm_location' => 'Shamba la Arusha',
                'farm_size_acres' => 5,
                'preferred_date' => '2025-06-15',
                'total_cost' => 75000,
                'status' => 'confirmed',
                'created_at' => '2025-06-06T10:00:00Z',
            ],
        ];

        return response()->json(['bookings' => $bookings]);
    }

    private function calculateCost($serviceId, $acres)
    {
        $prices = [
            'spraying' => 15000,
            'mapping' => 8000,
            'monitoring' => 5000,
            'seeding' => 12000,
        ];

        return ($prices[$serviceId] ?? 10000) * $acres;
    }
}
