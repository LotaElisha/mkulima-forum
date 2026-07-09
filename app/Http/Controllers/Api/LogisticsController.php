<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FreightRequest;
use App\Models\Transporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * EF-005 Logistics: transporter directory + freight request lifecycle.
 * Flow: open → quoted (transporter) → accepted (requester) → in_transit → delivered.
 * Requester may cancel while open/quoted; either side rating happens post-delivery.
 */
class LogisticsController extends Controller
{
    /**
     * Public directory of verified, available transporters.
     */
    public function transporters(Request $request): JsonResponse
    {
        $query = Transporter::with('user:id,uuid,name,avatar')
            ->where('verification_status', 'verified')
            ->where('is_available', true);

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->input('vehicle_type'));
        }
        if ($request->filled('region')) {
            $query->where('base_region', $request->input('region'));
        }
        if ($request->filled('min_capacity_kg')) {
            $query->where('capacity_kg', '>=', (float) $request->input('min_capacity_kg'));
        }

        $transporters = $query->orderByDesc('rating')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'transporters' => $transporters->items(),
            'pagination' => [
                'current_page' => $transporters->currentPage(),
                'last_page' => $transporters->lastPage(),
                'total' => $transporters->total(),
            ],
        ]);
    }

    /**
     * Register as a transporter (requires admin verification).
     */
    public function registerTransporter(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'vehicle_type' => ['required', 'in:bodaboda,bajaji,pickup,canter,lorry,refrigerated'],
            'plate_number' => ['nullable', 'string', 'max:20'],
            'capacity_kg' => ['nullable', 'numeric', 'min:0'],
            'base_region' => ['required', 'string', 'max:64'],
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['user_id'] = $user->id;

        $transporter = Transporter::firstOrCreate(
            ['user_id' => $user->id, 'vehicle_type' => $validated['vehicle_type']],
            $validated,
        );

        return response()->json([
            'message' => 'Transporter profile submitted for verification.',
            'transporter' => $transporter,
        ], 201);
    }

    /**
     * Create a freight request (open for quotes).
     */
    public function createFreight(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'pickup_location' => ['required', 'string', 'max:255'],
            'dropoff_location' => ['required', 'string', 'max:255'],
            'pickup_coords' => ['nullable', 'array'],
            'dropoff_coords' => ['nullable', 'array'],
            'cargo_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'cargo_description' => ['nullable', 'string', 'max:2000'],
            'pickup_at' => ['nullable', 'date', 'after:now'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);

        $freight = FreightRequest::create($validated + [
            'tenant_id' => $user->tenant_id,
            'requester_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Freight request posted. Transporters will send quotes.',
            'freight' => $freight,
        ], 201);
    }

    /**
     * List freight: requester's own by default; open board for transporters
     * (?as=transporter → open requests in their region plus their assigned jobs).
     */
    public function freight(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->input('as') === 'transporter') {
            $transporterIds = Transporter::where('user_id', $user->id)->pluck('id');
            $query = FreightRequest::with('requester:id,uuid,name,avatar')
                ->where(function ($q) use ($transporterIds) {
                    $q->where('status', 'open')
                      ->orWhereIn('transporter_id', $transporterIds);
                });
        } else {
            $query = FreightRequest::with('transporter.user:id,uuid,name,avatar')
                ->where('requester_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $freight = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'freight' => $freight->items(),
            'pagination' => [
                'current_page' => $freight->currentPage(),
                'last_page' => $freight->lastPage(),
                'total' => $freight->total(),
            ],
        ]);
    }

    /**
     * Verified transporter quotes an open freight request.
     */
    public function quoteFreight(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'quoted_fare' => ['required', 'numeric', 'min:0'],
        ]);

        $transporter = Transporter::where('user_id', $user->id)
            ->where('verification_status', 'verified')
            ->first();

        if (!$transporter) {
            return response()->json(['message' => 'You are not a verified transporter.'], 403);
        }

        return DB::transaction(function () use ($uuid, $transporter, $validated) {
            $freight = FreightRequest::where('uuid', $uuid)->lockForUpdate()->firstOrFail();

            if ($freight->status !== 'open') {
                return response()->json(['message' => 'Request is no longer open for quotes.'], 422);
            }
            if ($freight->requester_id === $transporter->user_id) {
                return response()->json(['message' => 'You cannot quote your own request.'], 422);
            }

            $freight->update([
                'transporter_id' => $transporter->id,
                'quoted_fare' => $validated['quoted_fare'],
                'status' => 'quoted',
            ]);

            return response()->json(['message' => 'Quote submitted.', 'freight' => $freight]);
        });
    }

    /**
     * Status transitions.
     * Requester: accepted (from quoted), cancelled (open/quoted).
     * Assigned transporter: in_transit (from accepted), delivered (from in_transit).
     */
    public function updateFreight(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $freight = FreightRequest::with('transporter')->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,in_transit,delivered,cancelled'],
        ]);

        $isRequester = $freight->requester_id === $user->id;
        $isTransporter = $freight->transporter && $freight->transporter->user_id === $user->id;

        if (!$isRequester && !$isTransporter) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        $transitions = [
            'accepted' => ['role' => 'requester', 'from' => ['quoted']],
            'cancelled' => ['role' => 'requester', 'from' => ['open', 'quoted']],
            'in_transit' => ['role' => 'transporter', 'from' => ['accepted']],
            'delivered' => ['role' => 'transporter', 'from' => ['in_transit']],
        ];

        $rule = $transitions[$validated['status']];
        $actorRole = $isRequester ? 'requester' : 'transporter';

        if ($rule['role'] !== $actorRole) {
            return response()->json(['message' => 'Transition not allowed for your role.'], 422);
        }
        if (!in_array($freight->status, $rule['from'])) {
            return response()->json(['message' => "Cannot move from {$freight->status} to {$validated['status']}."], 422);
        }

        $freight->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Freight updated.', 'freight' => $freight]);
    }

    /**
     * Requester rates a delivered freight; transporter aggregate rating updates.
     */
    public function rateFreight(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();
        $freight = FreightRequest::with('transporter')->where('uuid', $uuid)->firstOrFail();

        if ($freight->requester_id !== $user->id) {
            return response()->json(['message' => 'Not authorized.'], 403);
        }
        if ($freight->status !== 'delivered') {
            return response()->json(['message' => 'Only delivered freight can be rated.'], 422);
        }
        if ($freight->requester_rating !== null) {
            return response()->json(['message' => 'Freight already rated.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $freight->update([
            'requester_rating' => $validated['rating'],
            'requester_review' => $validated['review'] ?? null,
        ]);

        $transporter = $freight->transporter;
        $newCount = $transporter->rating_count + 1;
        $transporter->update([
            'rating' => (($transporter->rating * $transporter->rating_count) + $validated['rating']) / $newCount,
            'rating_count' => $newCount,
        ]);

        return response()->json(['message' => 'Asante kwa tathmini yako.']);
    }
}
