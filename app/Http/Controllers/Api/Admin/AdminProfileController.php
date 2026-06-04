<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminProfileController extends Controller
{
    /**
     * Get admin profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'status' => $user->status,
                'kyc_status' => $user->kyc_status,
                'preferred_language' => $user->preferred_language,
                'last_active_at' => $user->last_active_at,
                'created_at' => $user->created_at,
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Update admin profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'string', 'regex:/^255[0-9]{9}$/'],
            'preferred_language' => ['sometimes', 'string', 'in:sw,en,lg,rw,fr'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'preferred_language' => $user->preferred_language,
            ],
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        // Revoke all tokens except current
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();

        return response()->json([
            'message' => 'Password changed successfully. All other sessions have been logged out.',
        ]);
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'avatar' => $path,
        ]);
    }

    /**
     * Get admin activity log
     */
    public function activityLog(Request $request): JsonResponse
    {
        $user = $request->user();

        // Since we don't have a dedicated activity log table yet,
        // return recent orders, escrows, and KYC actions as proxy
        $activities = [];

        // Recent orders managed
        $recentOrders = \App\Models\Order::where('updated_at', '>=', now()->subDays(30))
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get(['uuid', 'status', 'updated_at']);

        foreach ($recentOrders as $order) {
            $activities[] = [
                'type' => 'order',
                'description' => "Order {$order->uuid} status changed to {$order->status}",
                'timestamp' => $order->updated_at,
            ];
        }

        // Recent user registrations
        $recentUsers = \App\Models\User::where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['name', 'role', 'created_at']);

        foreach ($recentUsers as $newUser) {
            $activities[] = [
                'type' => 'user',
                'description' => "New {$newUser->role} registered: {$newUser->name}",
                'timestamp' => $newUser->created_at,
            ];
        }

        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return response()->json([
            'activities' => array_slice($activities, 0, 50),
        ]);
    }
}
