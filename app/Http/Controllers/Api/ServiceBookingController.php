<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generic services engine: agronomist, veterinary and soil-testing
 * directory + booking (EF-004 / EF-007 / EF-008).
 */
class ServiceBookingController extends Controller
{
    /**
     * Public directory of verified providers, filterable by type/region.
     */
    public function providers(Request $request): JsonResponse
    {
        $query = ServiceProvider::with('user:id,uuid,name,avatar')
            ->where('is_active', true)
            ->where('verification_status', 'verified');

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }
        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'ilike', "%{$search}%")
                  ->orWhere('bio', 'ilike', "%{$search}%");
            });
        }

        $providers = $query->orderByDesc('rating')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'providers' => $providers->items(),
            'pagination' => [
                'current_page' => $providers->currentPage(),
                'last_page' => $providers->lastPage(),
                'total' => $providers->total(),
            ],
        ]);
    }

    public function provider(string $uuid): JsonResponse
    {
        $provider = ServiceProvider::with('user:id,uuid,name,avatar')
            ->where('uuid', $uuid)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json(['provider' => $provider]);
    }

    /**
     * Register as a provider (requires later admin verification).
     */
    public function registerProvider(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'service_type' => ['required', 'in:agronomist,veterinary,soil_testing'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'specializations' => ['nullable', 'array'],
            'region' => ['required', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:64'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'visit_fee' => ['nullable', 'numeric', 'min:0'],
            'availability' => ['nullable', 'array'],
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['user_id'] = $user->id;

        $provider = ServiceProvider::firstOrCreate(
            ['user_id' => $user->id, 'service_type' => $validated['service_type']],
            $validated,
        );

        return response()->json([
            'message' => 'Provider profile submitted for verification.',
            'provider' => $provider,
        ], 201);
    }

    /**
     * Farmer creates a booking.
     */
    public function createBooking(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'provider_uuid' => ['required', 'exists:service_providers,uuid'],
            'booking_type' => ['required', 'in:consultation,farm_visit,sample_collection'],
            'description' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'max:5120'],
        ]);

        $provider = ServiceProvider::where('uuid', $validated['provider_uuid'])
            ->where('is_active', true)
            ->where('verification_status', 'verified')
            ->firstOrFail();

        if ($provider->user_id === $user->id) {
            return response()->json(['message' => 'You cannot book yourself.'], 422);
        }

        $media = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $media[] = $file->store('service-bookings', 'public');
            }
        }

        $fee = $validated['booking_type'] === 'consultation'
            ? $provider->consultation_fee
            : $provider->visit_fee;

        $booking = ServiceBooking::create([
            'tenant_id' => $user->tenant_id,
            'service_provider_id' => $provider->id,
            'farmer_id' => $user->id,
            'booking_type' => $validated['booking_type'],
            'description' => $validated['description'] ?? null,
            'media' => $media ?: null,
            'scheduled_at' => $validated['scheduled_at'],
            'location' => $validated['location'] ?? null,
            'fee' => $fee,
        ]);

        return response()->json([
            'message' => 'Booking created. The provider will confirm shortly.',
            'booking' => $booking->load('provider.user:id,uuid,name,avatar'),
        ], 201);
    }

    /**
     * Farmer's bookings, or provider's incoming bookings with ?as=provider.
     */
    public function bookings(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->input('as') === 'provider') {
            $providerIds = ServiceProvider::where('user_id', $user->id)->pluck('id');
            $query = ServiceBooking::with('farmer:id,uuid,name,avatar')
                ->whereIn('service_provider_id', $providerIds);
        } else {
            $query = ServiceBooking::with('provider.user:id,uuid,name,avatar')
                ->where('farmer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->orderByDesc('scheduled_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'bookings' => $bookings->items(),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    /**
     * Status transitions. Provider: confirm/in_progress/completed/no_show.
     * Farmer: cancel (while pending/confirmed).
     */
    public function updateBooking(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $booking = ServiceBooking::with('provider')->where('uuid', $uuid)->firstOrFail();

        $isProvider = $booking->provider->user_id === $user->id;
        $isFarmer = $booking->farmer_id === $user->id;

        if (!$isProvider && !$isFarmer) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,in_progress,completed,cancelled,no_show'],
            'provider_notes' => ['nullable', 'string', 'max:5000'],
            'results' => ['nullable', 'array'],
        ]);

        $allowed = $isProvider
            ? ['confirmed', 'in_progress', 'completed', 'no_show', 'cancelled']
            : ['cancelled'];

        if (!in_array($validated['status'], $allowed)) {
            return response()->json(['message' => 'Transition not allowed for your role.'], 422);
        }

        if ($isFarmer && !in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'Booking can no longer be cancelled.'], 422);
        }

        $booking->update([
            'status' => $validated['status'],
            'provider_notes' => $isProvider
                ? ($validated['provider_notes'] ?? $booking->provider_notes)
                : $booking->provider_notes,
            'results' => $isProvider
                ? ($validated['results'] ?? $booking->results)
                : $booking->results,
        ]);

        return response()->json(['message' => 'Booking updated.', 'booking' => $booking]);
    }

    /**
     * Farmer rates a completed booking; provider aggregate rating updates.
     */
    public function rateBooking(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $booking = ServiceBooking::with('provider')->where('uuid', $uuid)->firstOrFail();

        if ($booking->farmer_id !== $user->id) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'Only completed bookings can be rated.'], 422);
        }
        if ($booking->farmer_rating !== null) {
            return response()->json(['message' => 'Booking already rated.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update([
            'farmer_rating' => $validated['rating'],
            'farmer_review' => $validated['review'] ?? null,
        ]);

        $provider = $booking->provider;
        $newCount = $provider->rating_count + 1;
        $provider->update([
            'rating' => (($provider->rating * $provider->rating_count) + $validated['rating']) / $newCount,
            'rating_count' => $newCount,
        ]);

        return response()->json(['message' => 'Asante kwa tathmini yako.']);
    }
}
