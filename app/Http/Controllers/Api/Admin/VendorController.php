<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * List all vendors/agrodealers
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['agrodealer', 'seller'])
            ->with('tenant')
            ->withAvg('products', 'rating')
            ->latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('kyc_status')) {
            $query->where('kyc_status', $request->input('kyc_status'));
        }

        $vendors = $query->paginate(50);

        return response()->json([
            'vendors' => $vendors,
        ]);
    }

    /**
     * Show vendor details with products and ratings
     */
    public function show(string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->with(['tenant', 'products', 'orders'])
            ->firstOrFail();

        $stats = [
            'total_products' => $vendor->products()->count(),
            'total_orders' => $vendor->orders()->count(),
            'total_revenue' => $vendor->orders()->where('status', 'completed')->sum('total'),
            'avg_rating' => $vendor->products()->avg('rating') ?? 0,
            'rating_count' => $vendor->products()->sum('rating_count'),
        ];

        return response()->json([
            'vendor' => $vendor,
            'stats' => $stats,
        ]);
    }

    /**
     * Update vendor
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $vendor->id],
            'phone' => ['sometimes', 'string', 'regex:/^255[0-9]{9}$/', 'unique:users,phone,' . $vendor->id],
            'status' => ['sometimes', 'string', 'in:active,suspended,terminated'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_location' => ['nullable', 'string'],
            'business_license' => ['nullable', 'string'],
            'store_description' => ['nullable', 'string'],
        ]);

        $vendor->update($validated);

        return response()->json([
            'message' => 'Vendor updated successfully.',
            'vendor' => $vendor,
        ]);
    }

    /**
     * Suspend vendor
     */
    public function suspend(string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->firstOrFail();

        $vendor->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => request()->input('reason', 'Violation of platform policies'),
        ]);

        // Suspend all products
        $vendor->products()->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Vendor suspended successfully. All products deactivated.',
            'vendor' => $vendor,
        ]);
    }

    /**
     * Reactivate vendor
     */
    public function reactivate(string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->firstOrFail();

        $vendor->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return response()->json([
            'message' => 'Vendor reactivated successfully.',
            'vendor' => $vendor,
        ]);
    }

    /**
     * Get vendor ratings/reviews
     */
    public function reviews(string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->firstOrFail();

        // Get reviews from order items with ratings
        $reviews = []; // Would join with reviews table

        return response()->json([
            'vendor' => [
                'uuid' => $vendor->uuid,
                'name' => $vendor->name,
            ],
            'reviews' => $reviews,
            'avg_rating' => $vendor->products()->avg('rating') ?? 0,
        ]);
    }
}
