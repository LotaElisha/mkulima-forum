<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Login with email and password (for admin dashboard)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Account is not active.',
            ], 403);
        }

        $token = $user->createToken('admin-dashboard', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Request OTP for phone verification
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'purpose' => ['nullable', 'string', 'in:login,register,reset'],
        ]);

        $phone = $request->input('phone');
        $purpose = $request->input('purpose', 'login');

        if ($this->otpService->isRateLimited($phone)) {
            return response()->json([
                'message' => 'Too many OTP requests. Please try again later.',
            ], 429);
        }

        $result = $this->otpService->generate($phone, $purpose);

        // In production, send actual SMS
        // $this->otpService->sendSms($phone, "Your MkulimaForum code: {$result['code']}");

        // For development, return code in response
        return response()->json([
            'message' => $result['message'],
            'expires_in' => $result['expires_in'],
            'dev_code' => $result['code'], // Remove in production
        ]);
    }

    /**
     * Verify OTP and login/register user
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'code' => ['required', 'string', 'size:6'],
            'purpose' => ['nullable', 'string', 'in:login,register,reset'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:farmer,agrodealer'],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $phone = $request->input('phone');
        $code = $request->input('code');
        $purpose = $request->input('purpose', 'login');

        if (!$this->otpService->verify($phone, $code, $purpose)) {
            return response()->json([
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        // Find or create user
        $user = User::where('phone', $phone)->first();

        if (!$user && $purpose === 'login') {
            return response()->json([
                'message' => 'User not found. Please register first.',
            ], 404);
        }

        if (!$user && $purpose === 'register') {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'country_code' => ['required', 'string', 'size:2'],
            ]);

            $tenantId = match ($request->input('country_code')) {
                'tz' => 1,
                'ke' => 2,
                'ug' => 3,
                'rw' => 4,
                default => 1,
            };

            $user = User::create([
                'tenant_id' => $tenantId,
                'phone' => $phone,
                'name' => $request->input('name'),
                'role' => $request->input('role', 'farmer'),
                'phone_verified_at' => now(),
                'preferred_language' => 'sw',
            ]);

            // Assign default role using Spatie
            $user->assignRole($user->role);
        }

        if ($user) {
            $user->update([
                'phone_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        }

        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Authentication successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400 * 30, // 30 days
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $user->role,
                'kyc_status' => $user->kyc_status,
                'preferred_language' => $user->preferred_language,
            ],
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $user->role,
                'kyc_status' => $user->kyc_status,
                'preferred_language' => $user->preferred_language,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'preferred_language' => ['sometimes', 'string', 'in:sw,en,lg,rw,fr'],
            'avatar' => ['sometimes', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'preferred_language' => $user->preferred_language,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }
}
