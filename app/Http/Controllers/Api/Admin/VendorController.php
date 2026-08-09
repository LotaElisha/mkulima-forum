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
     * Register / Onboard new Vendor, Agrodealer, Agrovet, Supplier, or Partner
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'role' => ['required', 'string', 'in:agrodealer,seller,supplier,partner'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_location' => ['nullable', 'string', 'max:255'],
            'business_license' => ['nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
            'kyc_status' => ['nullable', 'string', 'in:verified,pending,unverified'],
        ]);

        $password = $validated['password'] ?? 'password123';

        $vendor = User::create([
            'tenant_id' => $request->user()->tenant_id ?? 1,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'store_name' => $validated['store_name'] ?? $validated['name'],
            'store_location' => $validated['store_location'] ?? null,
            'business_license' => $validated['business_license'] ?? null,
            'store_description' => $validated['store_description'] ?? null,
            'password' => bcrypt($password),
            'status' => 'active',
            'kyc_status' => $validated['kyc_status'] ?? 'verified',
        ]);

        return response()->json([
            'message' => 'Vendor / Partner registered successfully.',
            'vendor' => $vendor,
        ], 201);
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
            'email' => ['sometimes', 'email', 'unique:users,email,'.$vendor->id],
            'phone' => ['sometimes', 'string', 'regex:/^255[0-9]{9}$/', 'unique:users,phone,'.$vendor->id],
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

    /**
     * Delete vendor permanently
     */
    public function destroy(string $uuid): JsonResponse
    {
        $vendor = User::where('uuid', $uuid)
            ->whereIn('role', ['agrodealer', 'seller'])
            ->firstOrFail();

        // Revoke all tokens and delete
        $vendor->tokens()->delete();
        $vendor->products()->delete();
        $vendor->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully.',
        ]);
    }
}
