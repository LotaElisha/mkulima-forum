<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\SmsService;
use App\Services\Spine\ConfigRegistry;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    protected OtpService $otpService;

    protected SmsService $smsService;

    protected ConfigRegistry $configRegistry;

    public function __construct(OtpService $otpService, SmsService $smsService, ConfigRegistry $configRegistry)
    {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
        $this->configRegistry = $configRegistry;
    }

    /**
     * Login with email and password (mobile app)
     */
    public function loginWithEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Account is not active.',
            ], 403);
        }

        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        if ($request->header('X-Auth-Client') === 'web') {
            return response()->json([
                'message' => 'Login successful.',
                'user' => $this->userPayload($user),
            ])->cookie('user_token', $token, 480, '/api', null, app()->environment('production'), true, false, 'Lax');
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
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

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Account is not active.',
            ], 403);
        }

        if (! in_array($user->role, [Roles::ADMIN, Roles::SUPERADMIN], true)) {
            return response()->json(['message' => 'Administrator access required.'], 403);
        }

        $token = $user->createToken('admin-dashboard', ['*'])->plainTextToken;

        $response = response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);

        return $response->cookie(
            'admin_token',
            $token,
            480,
            '/api',
            null,
            app()->environment('production'),
            true,
            false,
            'Strict'
        );
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

        if (! $this->otpEnabled()) {
            return response()->json(['message' => 'OTP authentication is disabled.'], 503);
        }

        if ($this->otpService->isRateLimited($phone)) {
            return response()->json([
                'message' => 'Too many OTP requests. Please try again later.',
            ], 429);
        }

        if (app()->environment('production') && ! $this->smsService->isConfigured()) {
            return response()->json([
                'message' => 'OTP delivery is temporarily unavailable.',
            ], 503);
        }

        $result = $this->otpService->generate($phone, $purpose);

        if (! app()->environment('local', 'testing')) {
            $delivery = $this->smsService->send(
                $phone,
                "MkulimaForum verification code: {$result['code']}. It expires in 10 minutes.",
                'otp'
            );
            if (! ($delivery['success'] ?? false)) {
                return response()->json([
                    'message' => 'OTP delivery failed. Please try again later.',
                ], 503);
            }
        }

        $response = [
            'message' => $result['message'],
            'expires_in' => $result['expires_in'],
        ];

        // SECURITY: only expose the code in local/testing debug builds.
        // Leaking it in production lets anyone authenticate as any phone number.
        if (app()->environment('local', 'testing') && config('app.debug')) {
            $response['dev_code'] = $result['code'];
        }

        return response()->json($response);
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
            'role' => ['nullable', 'string', Roles::rule(Roles::SELF_REGISTERABLE)],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $phone = $request->input('phone');
        $code = $request->input('code');
        $purpose = $request->input('purpose', 'login');

        if (! $this->otpEnabled()) {
            return response()->json(['message' => 'OTP authentication is disabled.'], 503);
        }

        if ($this->otpService->isVerificationLimited($phone, $purpose)) {
            return response()->json(['message' => 'Too many invalid OTP attempts. Try again later.'], 429);
        }

        if (! $this->otpService->verify($phone, $code, $purpose)) {
            return response()->json([
                'message' => 'Invalid or expired OTP code.',
            ], 422);
        }

        // Find or create user
        $user = User::where('phone', $phone)->first();

        if (! $user && $purpose === 'login') {
            return response()->json([
                'message' => 'User not found. Please register first.',
            ], 404);
        }

        if (! $user && $purpose === 'register') {
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

            // Assign Spatie role; firstOrCreate guards against an unseeded DB
            // (assignRole throws RoleDoesNotExist otherwise).
            $user->assignRole(
                Role::firstOrCreate([
                    'name' => $user->role,
                    'guard_name' => 'web',
                ])
            );
        }

        if ($user) {
            $user->update([
                'phone_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        }

        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        if ($request->header('X-Auth-Client') === 'web') {
            return response()->json([
                'message' => 'Authentication successful.',
                'user' => $this->userPayload($user),
            ])->cookie('user_token', $token, 480, '/api', null, app()->environment('production'), true, false, 'Lax');
        }

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

    private function otpEnabled(): bool
    {
        return (bool) $this->configRegistry->get('auth.otp_enabled', ! app()->environment('production'));
    }

    private function userPayload(User $user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->role,
            'kyc_status' => $user->kyc_status,
            'preferred_language' => $user->preferred_language,
        ];
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
            'email' => ['sometimes', 'email', 'unique:users,email,'.$user->id],
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
        ])->withoutCookie('admin_token', '/api')->withoutCookie('user_token', '/api');
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ])->withoutCookie('admin_token', '/api')->withoutCookie('user_token', '/api');
    }
}
