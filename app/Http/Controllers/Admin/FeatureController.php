<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Get all feature flags
     */
    public function index(): JsonResponse
    {
        $features = FeatureFlag::all()->mapWithKeys(function ($feature) {
            return [$feature->key => [
                'enabled' => $feature->enabled,
                'name' => $feature->name,
                'description' => $feature->description,
                'category' => $feature->category,
            ]];
        });

        return response()->json([
            'features' => $features,
        ]);
    }

    /**
     * Toggle a feature flag
     */
    public function toggle(Request $request, string $key): JsonResponse
    {
        $feature = FeatureFlag::where('key', $key)->firstOrFail();
        $feature->update(['enabled' => !$feature->enabled]);

        return response()->json([
            'message' => "Feature '{$feature->name}' is now " . ($feature->enabled ? 'enabled' : 'disabled'),
            'feature' => [
                'key' => $feature->key,
                'enabled' => $feature->enabled,
            ],
        ]);
    }

    /**
     * Update feature settings
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $feature = FeatureFlag::where('key', $key)->firstOrFail();
        
        $validated = $request->validate([
            'enabled' => 'sometimes|boolean',
            'settings' => 'sometimes|array',
        ]);

        $feature->update($validated);

        return response()->json([
            'message' => 'Feature updated successfully',
            'feature' => $feature,
        ]);
    }

    /**
     * Get features by category
     */
    public function byCategory(string $category): JsonResponse
    {
        $features = FeatureFlag::where('category', $category)->get();

        return response()->json([
            'features' => $features,
        ]);
    }

    /**
     * Check if a feature is enabled
     */
    public function check(string $key): JsonResponse
    {
        $feature = FeatureFlag::where('key', $key)->first();

        return response()->json([
            'enabled' => $feature ? $feature->enabled : false,
        ]);
    }

    /**
     * Get public feature status (for mobile app)
     */
    public function publicStatus(): JsonResponse
    {
        $features = FeatureFlag::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($feature) {
                return [$feature->key => $feature->enabled];
            });

        return response()->json([
            'features' => $features,
        ]);
    }
}
