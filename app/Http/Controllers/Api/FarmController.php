<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\FarmActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    /**
     * List user's registered farms
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Farm::where('user_id', $user->id)
            ->withCount('activities')
            ->withSum('activities', 'cost_tzs')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('crop_type', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $farms = $query->get();

        return response()->json([
            'farms' => $farms,
            'summary' => [
                'total_farms' => $farms->count(),
                'total_acres' => $farms->sum('size_acres'),
                'active_farms' => $farms->where('status', 'active')->count(),
                'total_expenses' => $farms->sum('activities_sum_cost_tzs') ?? 0,
            ],
        ]);
    }

    /**
     * Register a new farm
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'size_acres' => ['required', 'numeric', 'min:0.1'],
            'crop_type' => ['required', 'string', 'max:255'],
            'soil_type' => ['nullable', 'string', 'max:255'],
            'planting_date' => ['nullable', 'date'],
            'harvest_expected_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:active,harvesting,fallow,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        $farm = Farm::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'location' => $validated['location'],
            'size_acres' => $validated['size_acres'],
            'crop_type' => $validated['crop_type'],
            'soil_type' => $validated['soil_type'] ?? null,
            'planting_date' => $validated['planting_date'] ?? null,
            'harvest_expected_date' => $validated['harvest_expected_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Farm registered successfully.',
            'farm' => $farm->load('activities'),
        ], 201);
    }

    /**
     * Show single farm with activity log
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $farm = Farm::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['activities' => function ($q) {
                $q->orderBy('activity_date', 'desc');
            }])
            ->firstOrFail();

        return response()->json([
            'farm' => $farm,
            'stats' => [
                'total_activities' => $farm->activities->count(),
                'total_expenditure' => $farm->activities->sum('cost_tzs'),
            ],
        ]);
    }

    /**
     * Update farm details
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $farm = Farm::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:255'],
            'size_acres' => ['sometimes', 'numeric', 'min:0.1'],
            'crop_type' => ['sometimes', 'string', 'max:255'],
            'soil_type' => ['nullable', 'string', 'max:255'],
            'planting_date' => ['nullable', 'date'],
            'harvest_expected_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'in:active,harvesting,fallow,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        $farm->update($validated);

        return response()->json([
            'message' => 'Farm details updated successfully.',
            'farm' => $farm->fresh('activities'),
        ]);
    }

    /**
     * Delete farm
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $farm = Farm::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $farm->delete();

        return response()->json([
            'message' => 'Farm removed successfully.',
        ]);
    }

    /**
     * Add activity log entry to farm
     */
    public function storeActivity(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $farm = Farm::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'activity_type' => ['required', 'string', 'max:255'],
            'activity_date' => ['required', 'date'],
            'cost_tzs' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $activity = $farm->activities()->create([
            'activity_type' => $validated['activity_type'],
            'activity_date' => $validated['activity_date'],
            'cost_tzs' => $validated['cost_tzs'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Activity logged successfully.',
            'activity' => $activity,
            'farm' => $farm->fresh('activities'),
        ], 201);
    }

    /**
     * Delete activity log entry
     */
    public function destroyActivity(Request $request, string $activityUuid): JsonResponse
    {
        $user = $request->user();

        $activity = FarmActivity::where('uuid', $activityUuid)
            ->whereHas('farm', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->firstOrFail();

        $activity->delete();

        return response()->json([
            'message' => 'Activity record deleted successfully.',
        ]);
    }
}
