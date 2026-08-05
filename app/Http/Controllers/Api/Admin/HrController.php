<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HrController extends Controller
{
    /**
     * List all staff/employees (users with staff roles)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
            ->with('tenant')
            ->latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $staff = $query->paginate(50);

        return response()->json([
            'staff' => $staff,
        ]);
    }

    /**
     * Create new staff member
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/', 'unique:users'],
            'role' => ['required', 'string', 'in:admin,agronomist,veterinary,logistics,support'],
            'password' => ['required', 'string', 'min:8'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'joining_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';
        $validated['kyc_status'] = 'verified';
        $validated['phone_verified_at'] = now();
        $validated['email_verified_at'] = now();

        $user = User::create($validated);
        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'Staff member created successfully.',
            'staff' => $user,
        ], 201);
    }

    /**
     * Show staff member details
     */
    public function show(string $uuid): JsonResponse
    {
        $staff = User::where('uuid', $uuid)
            ->whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
            ->with('tenant')
            ->firstOrFail();

        return response()->json([
            'staff' => $staff,
            'permissions' => $staff->getAllPermissions()->pluck('name'),
            'roles' => $staff->getRoleNames(),
        ]);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $staff = User::where('uuid', $uuid)
            ->whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $staff->id],
            'phone' => ['sometimes', 'string', 'regex:/^255[0-9]{9}$/', 'unique:users,phone,' . $staff->id],
            'role' => ['sometimes', 'string', 'in:admin,agronomist,veterinary,logistics,support'],
            'status' => ['sometimes', 'string', 'in:active,suspended,terminated'],
            'department' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'joining_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string'],
        ]);

        if (isset($validated['role']) && $validated['role'] !== $staff->role) {
            $staff->syncRoles([$validated['role']]);
        }

        $staff->update($validated);

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'staff' => $staff,
        ]);
    }

    /**
     * Delete/terminate staff member
     */
    public function destroy(string $uuid): JsonResponse
    {
        $staff = User::where('uuid', $uuid)
            ->whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
            ->firstOrFail();

        // Soft delete by suspending
        $staff->update([
            'status' => 'terminated',
            'is_active' => false,
        ]);

        // Revoke all tokens
        $staff->tokens()->delete();

        return response()->json([
            'message' => 'Staff member terminated successfully.',
        ]);
    }

    /**
     * Get HR statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_staff' => User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])->count(),
            'by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'agronomist' => User::where('role', 'agronomist')->count(),
                'veterinary' => User::where('role', 'veterinary')->count(),
                'logistics' => User::where('role', 'logistics')->count(),
                'support' => User::where('role', 'support')->count(),
            ],
            'by_status' => [
                'active' => User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])->where('status', 'active')->count(),
                'suspended' => User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])->where('status', 'suspended')->count(),
                'terminated' => User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])->where('status', 'terminated')->count(),
            ],
            'new_this_month' => User::whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Assign permissions to staff
     */
    public function assignPermissions(Request $request, string $uuid): JsonResponse
    {
        $staff = User::where('uuid', $uuid)
            ->whereIn('role', ['admin', 'agronomist', 'veterinary', 'logistics', 'support'])
            ->firstOrFail();

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $staff->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permissions updated successfully.',
            'permissions' => $staff->getAllPermissions()->pluck('name'),
        ]);
    }
}
