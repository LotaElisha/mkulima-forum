<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DroneBooking;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DroneController extends Controller
{
    /**
     * Platform-defined drone service offerings (config, not user data).
     * Prices are indicative and confirmed by the operator before dispatch.
     */
    public const SERVICES = [
        'spraying' => [
            'id' => 'spraying',
            'name' => 'Drone Spraying',
            'description' => 'Puliza dawa za wadudu au mbolea kutoka juu kwa usahihi mkubwa',
            'price_per_acre' => 15000,
            'duration' => '30 min / acre',
            'icon' => 'spray',
        ],
        'mapping' => [
            'id' => 'mapping',
            'name' => 'Aerial Mapping',
            'description' => 'Piga picha za shamba lako kutoka juu kwa mahesabu na mpango',
            'price_per_acre' => 8000,
            'duration' => '20 min / acre',
            'icon' => 'map',
        ],
        'monitoring' => [
            'id' => 'monitoring',
            'name' => 'Crop Monitoring',
            'description' => 'Fuatilia afya ya mimea yako kila wiki kwa picha za infrared',
            'price_per_acre' => 5000,
            'duration' => '15 min / acre',
            'icon' => 'monitor',
        ],
        'seeding' => [
            'id' => 'seeding',
            'name' => 'Drone Seeding',
            'description' => 'Panda mbegu kwa usahihi mkubwa kutoka juu',
            'price_per_acre' => 12000,
            'duration' => '25 min / acre',
            'icon' => 'seed',
        ],
    ];

    public function services(): JsonResponse
    {
        if (!FeatureFlag::isEnabled('drone_services')) {
            return $this->unavailable();
        }

        return response()->json([
            'services' => array_values(self::SERVICES),
            'note' => 'Bei ni makadirio; gharama kamili itathibitishwa na mtoa huduma kabla ya kazi.',
        ]);
    }

    public function book(Request $request): JsonResponse
    {
        if (!FeatureFlag::isEnabled('drone_services')) {
            return $this->unavailable();
        }

        $validated = $request->validate([
            'service_id' => 'required|string|in:' . implode(',', array_keys(self::SERVICES)),
            'farm_location' => 'required|string|max:255',
            'farm_size_acres' => 'required|numeric|min:0.5|max:10000',
            'preferred_date' => 'required|date|after:today',
            'contact_phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        $booking = DroneBooking::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'service_id' => $validated['service_id'],
            'farm_location' => $validated['farm_location'],
            'farm_size_acres' => $validated['farm_size_acres'],
            'preferred_date' => $validated['preferred_date'],
            'contact_phone' => $validated['contact_phone'],
            'notes' => $validated['notes'] ?? null,
            'total_cost' => self::SERVICES[$validated['service_id']]['price_per_acre'] * $validated['farm_size_acres'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Ombi la drone limepokelewa. Tutakupigia kuthibitisha.',
            'booking' => $this->present($booking),
        ], 201);
    }

    public function myBookings(Request $request): JsonResponse
    {
        if (!FeatureFlag::isEnabled('drone_services')) {
            return $this->unavailable();
        }

        $bookings = DroneBooking::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'bookings' => $bookings->map(fn ($b) => $this->present($b)),
            'total' => $bookings->total(),
        ]);
    }

    protected function present(DroneBooking $booking): array
    {
        $service = self::SERVICES[$booking->service_id] ?? null;

        return [
            'id' => $booking->uuid,
            'service_id' => $booking->service_id,
            'service_name' => $service['name'] ?? $booking->service_id,
            'farm_location' => $booking->farm_location,
            'farm_size_acres' => (float) $booking->farm_size_acres,
            'preferred_date' => $booking->preferred_date->toDateString(),
            'contact_phone' => $booking->contact_phone,
            'notes' => $booking->notes,
            'total_cost' => (float) $booking->total_cost,
            'status' => $booking->status,
            'created_at' => $booking->created_at->toIso8601String(),
        ];
    }

    protected function unavailable(): JsonResponse
    {
        return response()->json([
            'message' => 'Huduma ya drone bado haijazinduliwa katika eneo lako.',
            'available' => false,
        ], 503);
    }
}
