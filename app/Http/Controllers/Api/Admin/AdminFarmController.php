<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFarmController extends Controller
{
    /**
     * Admin view of all farms across system
     */
    public function index(Request $request): JsonResponse
    {
        $query = Farm::with(['user:id,uuid,name,phone,email', 'activities'])
            ->withCount('activities')
            ->withSum('activities', 'cost_tzs')
            ->latest();

        if ($request->has('crop_type')) {
            $query->where('crop_type', 'like', "%{$request->input('crop_type')}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('crop_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $farms = $query->paginate(50);

        return response()->json([
            'farms' => $farms,
            'summary' => [
                'total_farms' => Farm::count(),
                'total_acres' => Farm::sum('size_acres'),
                'active_farms' => Farm::where('status', 'active')->count(),
                'primary_crops' => Farm::selectRaw('crop_type, COUNT(*) as count')
                    ->groupBy('crop_type')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }

    /**
     * Admin register farm for a specific farmer/user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
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

        $farm = Farm::create($validated);

        return response()->json([
            'message' => 'Farm registered by admin successfully.',
            'farm' => $farm->load('user'),
        ], 201);
    }
}
