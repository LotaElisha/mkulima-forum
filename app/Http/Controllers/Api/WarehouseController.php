<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseBooking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * EF-006 Warehouse: storage directory + booking lifecycle.
 * Booking: pending → confirmed (operator, capacity reserved) → stored →
 * withdrawn (capacity released). Farmer may cancel while pending/confirmed
 * (confirmed cancel releases capacity).
 */
class WarehouseController extends Controller
{
    /**
     * Public directory of verified, active warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::with('operator:id,uuid,name,avatar')
            ->where('is_active', true)
            ->where('verification_status', 'verified');

        if ($request->filled('storage_type')) {
            $query->where('storage_type', $request->input('storage_type'));
        }
        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }
        if ($request->filled('min_available_tons')) {
            $query->where('available_tons', '>=', (float) $request->input('min_available_tons'));
        }

        $warehouses = $query->orderBy('price_per_ton_month')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'warehouses' => $warehouses->items(),
            'pagination' => [
                'current_page' => $warehouses->currentPage(),
                'last_page' => $warehouses->lastPage(),
                'total' => $warehouses->total(),
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $warehouse = Warehouse::with('operator:id,uuid,name,avatar')
            ->where('uuid', $uuid)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json(['warehouse' => $warehouse]);
    }

    /**
     * Register a warehouse (requires admin verification).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'storage_type' => ['required', 'in:dry,cold,grain_silo,general'],
            'region' => ['required', 'string', 'max:64'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity_tons' => ['required', 'numeric', 'min:0.1'],
            'price_per_ton_month' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
        ]);

        $warehouse = Warehouse::create($validated + [
            'tenant_id' => $user->tenant_id,
            'operator_id' => $user->id,
            'available_tons' => $validated['capacity_tons'],
        ]);

        return response()->json([
            'message' => 'Warehouse submitted for verification.',
            'warehouse' => $warehouse,
        ], 201);
    }

    /**
     * Farmer books storage; cost = tons × price/ton/month × months (ceil).
     */
    public function createBooking(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'warehouse_uuid' => ['required', 'exists:warehouses,uuid'],
            'produce_type' => ['required', 'string', 'max:64'],
            'quantity_tons' => ['required', 'numeric', 'min:0.1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            $warehouse = Warehouse::where('uuid', $validated['warehouse_uuid'])
                ->where('is_active', true)
                ->where('verification_status', 'verified')
                ->lockForUpdate()
                ->firstOrFail();

            if ($warehouse->operator_id === $user->id) {
                return response()->json(['message' => 'You cannot book your own warehouse.'], 422);
            }
            if ((float) $warehouse->available_tons < (float) $validated['quantity_tons']) {
                return response()->json([
                    'message' => 'Insufficient capacity.',
                    'available_tons' => $warehouse->available_tons,
                ], 422);
            }

            $months = max(1, (int) ceil(
                Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) / 30
            ));

            $booking = WarehouseBooking::create([
                'tenant_id' => $user->tenant_id,
                'warehouse_id' => $warehouse->id,
                'farmer_id' => $user->id,
                'produce_type' => $validated['produce_type'],
                'quantity_tons' => $validated['quantity_tons'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_cost' => $validated['quantity_tons'] * $warehouse->price_per_ton_month * $months,
            ]);

            return response()->json([
                'message' => 'Booking created. The operator will confirm shortly.',
                'booking' => $booking->load('warehouse:id,uuid,name,region'),
            ], 201);
        });
    }

    /**
     * Farmer's bookings, or operator's incoming with ?as=operator.
     */
    public function bookings(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->input('as') === 'operator') {
            $warehouseIds = Warehouse::where('operator_id', $user->id)->pluck('id');
            $query = WarehouseBooking::with('farmer:id,uuid,name,avatar', 'warehouse:id,uuid,name')
                ->whereIn('warehouse_id', $warehouseIds);
        } else {
            $query = WarehouseBooking::with('warehouse:id,uuid,name,region')
                ->where('farmer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query->orderByDesc('created_at')
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
     * Status transitions with capacity accounting.
     * Operator: confirmed (reserves tons), stored, withdrawn (releases tons).
     * Farmer: cancelled while pending/confirmed (confirmed cancel releases tons).
     */
    public function updateBooking(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,stored,withdrawn,cancelled'],
        ]);

        return DB::transaction(function () use ($user, $uuid, $validated) {
            $booking = WarehouseBooking::where('uuid', $uuid)->lockForUpdate()->firstOrFail();
            $warehouse = Warehouse::where('id', $booking->warehouse_id)->lockForUpdate()->first();

            $isOperator = $warehouse->operator_id === $user->id;
            $isFarmer = $booking->farmer_id === $user->id;

            if (!$isOperator && !$isFarmer) {
                return response()->json(['message' => 'Not authorized.'], 403);
            }

            $transitions = [
                'confirmed' => ['role' => 'operator', 'from' => ['pending']],
                'stored' => ['role' => 'operator', 'from' => ['confirmed']],
                'withdrawn' => ['role' => 'operator', 'from' => ['stored']],
                'cancelled' => ['role' => 'farmer', 'from' => ['pending', 'confirmed']],
            ];

            $rule = $transitions[$validated['status']];
            $actorRole = $isOperator ? 'operator' : 'farmer';

            if ($rule['role'] !== $actorRole) {
                return response()->json(['message' => 'Transition not allowed for your role.'], 422);
            }
            if (!in_array($booking->status, $rule['from'])) {
                return response()->json(['message' => "Cannot move from {$booking->status} to {$validated['status']}."], 422);
            }

            // Capacity accounting
            if ($validated['status'] === 'confirmed') {
                if ((float) $warehouse->available_tons < (float) $booking->quantity_tons) {
                    return response()->json(['message' => 'Insufficient capacity to confirm.'], 422);
                }
                $warehouse->decrement('available_tons', $booking->quantity_tons);
            }
            if ($validated['status'] === 'withdrawn'
                || ($validated['status'] === 'cancelled' && $booking->status === 'confirmed')) {
                $warehouse->increment('available_tons', $booking->quantity_tons);
            }

            $booking->update(['status' => $validated['status']]);

            return response()->json(['message' => 'Booking updated.', 'booking' => $booking->fresh()]);
        });
    }
}
