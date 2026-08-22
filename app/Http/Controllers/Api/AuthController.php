<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\SocialIdentityVerifier;
use App\Services\OtpService;
use App\Services\SmsService;
use App\Services\Spine\ConfigRegistry;
use App\Support\Roles;
use App\Support\UploadRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
            ])->cookie('user_token', $token, self::cookieLifetimeMinutes(), '/api', null, app()->environment('production'), true, false, 'Lax');
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

    /** Register with email and password. Phone OTP remains an optional method. */
    public function registerWithEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:users,pending_email'],
            'password' => ['required', 'confirmed', ...PasswordController::passwordRules()],
            'role' => ['nullable', 'string', Roles::rule(Roles::SELF_REGISTERABLE)],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            // provision(), not create(): role, status and password are no
            // longer mass-assignable. The role here is user-selected but
            // validated against Roles::SELF_REGISTERABLE above, so it can
            // never be 'admin'.
            $user = User::provision([
                'tenant_id' => $this->tenantId($validated['country_code'] ?? 'tz'),
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? Roles::FARMER,
                'status' => 'active',
                'preferred_language' => 'sw',
            ]);
            $this->assignUserRole($user);

            return $user;
        });

        // Queued, so an unreachable SMTP host cannot hold the sign-up response
        // open — the account exists either way and the link can be re-sent.
        $user->sendEmailVerificationNotification();

        return $this->authenticatedResponse($request, $user, 'Registration successful.');
    }

    /** Exchange a provider-issued identity token for a MkulimaForum session. */
    public function social(Request $request, SocialIdentityVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:google,apple'],
            'identity_token' => ['required', 'string', 'max:10000'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', Roles::rule(Roles::SELF_REGISTERABLE)],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);
        $identity = $verifier->verify($validated['provider'], $validated['identity_token']);

        $user = DB::transaction(function () use ($validated, $identity) {
            $account = SocialAccount::where('provider', $validated['provider'])
                ->where('provider_user_id', $identity['id'])
                ->first();
            if ($account) {
                return $account->user;
            }

            $email = $identity['email'] ?? null;
            if (! $email) {
                throw ValidationException::withMessages([
                    'identity_token' => 'The provider did not supply an email address. Re-authorize and share your email.',
                ]);
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::provision([
                    'tenant_id' => $this->tenantId($validated['country_code'] ?? 'tz'),
                    'name' => $validated['name'] ?? $identity['name'] ?? Str::before($email, '@'),
                    'email' => $email,
                    // The provider already proved this address.
                    'email_verified_at' => now(),
                    'avatar' => $identity['avatar'],
                    'role' => $validated['role'] ?? Roles::FARMER,
                    'status' => 'active',
                    'preferred_language' => 'sw',
                ]);
                $this->assignUserRole($user);
            }

            $user->socialAccounts()->create([
                'provider' => $validated['provider'],
                'provider_user_id' => $identity['id'],
                'email' => $email,
            ]);

            return $user;
        });

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is not active.'], 403);
        }

        return $this->authenticatedResponse($request, $user, 'Social authentication successful.');
    }

    /** Return Apple's Android web flow to the official app callback activity. */
    public function appleAndroidCallback(Request $request): RedirectResponse
    {
        $payload = $request->only(['code', 'id_token', 'state', 'user', 'error']);
        $query = http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
        $package = config('services.social.android_package', 'app.mkulimaforum.mobile');

        return redirect()->away("intent://callback?{$query}#Intent;package={$package};scheme=signinwithapple;end");
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
            self::cookieLifetimeMinutes(),
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

        // Duplicate-account prevention.
        //
        // If this request carries a valid token the caller has already told us
        // who they are, so an unclaimed number attaches to THAT account rather
        // than starting a second one. A number belonging to somebody else is
        // refused, never merged: merging moves farm records and wallet
        // balances between accounts, and must not be triggerable by a stranger
        // who happens to control a handset.
        // Explicitly the sanctum guard: this route sits outside auth:sanctum,
        // so the default guard would not resolve a bearer token here.
        $currentUser = $request->user('sanctum');
        $owner = User::where('phone', $phone)->first();

        if ($owner && $currentUser && $owner->id !== $currentUser->id) {
            return response()->json(['message' => __('auth_flows.phone_taken')], 422);
        }

        $user = $owner ?? $currentUser;

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

            $user = User::provision([
                'tenant_id' => $this->tenantId((string) $request->input('country_code', 'tz')),
                'phone' => $phone,
                'name' => $request->input('name'),
                'role' => $request->input('role', 'farmer'),
                'status' => 'active',
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
            // 'phone' is included because $user may be the signed-in account
            // that did not have this number until a moment ago.
            $user->setPrivileged([
                'phone' => $phone,
                'phone_verified_at' => now(),
                'last_active_at' => now(),
            ]);
        }

        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        if ($request->header('X-Auth-Client') === 'web') {
            return response()->json([
                'message' => 'Authentication successful.',
                'user' => $this->userPayload($user),
            ])->cookie('user_token', $token, self::cookieLifetimeMinutes(), '/api', null, app()->environment('production'), true, false, 'Lax');
        }

        return response()->json([
            'message' => 'Authentication successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            // Reported from config, never guessed. This used to advertise 30
            // days while config/sanctum.php expired tokens after 8 hours, so
            // the app believed it was signed in and every request silently
            // 401'd until the user force-quit it.
            'expires_in' => self::tokenLifetimeSeconds(),
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

    private function authenticatedResponse(Request $request, User $user, string $message): JsonResponse
    {
        $token = $user->createToken($request->header('X-Auth-Client') === 'web' ? 'web-app' : 'mobile-app', ['*'])->plainTextToken;
        $response = response()->json([
            'message' => $message,
            'token' => $request->header('X-Auth-Client') === 'web' ? null : $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);

        return $request->header('X-Auth-Client') === 'web'
            ? $response->cookie('user_token', $token, 480, '/api', null, app()->environment('production'), true, false, 'Lax')
            : $response;
    }

    /**
     * Resolve the tenant for a country code.
     *
     * This used to be a hardcoded match returning 1-4, which assumed the
     * tenants table had been seeded in exactly one order and never edited. On
     * a production database where that did not hold, every registration died
     * on a foreign key violation — a 500 on the first screen of the product.
     * tenants.country_code is unique, so look it up.
     */
    private function tenantId(string $countryCode): int
    {
        $code = strtolower($countryCode);

        $tenant = Tenant::where('country_code', $code)->first()
            ?? Tenant::where('country_code', 'tz')->first()
            ?? Tenant::query()->orderBy('id')->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'country_code' => 'Registration is not open for this country yet.',
            ]);
        }

        return (int) $tenant->id;
    }

    private function assignUserRole(User $user): void
    {
        $user->assignRole(Role::firstOrCreate(['name' => $user->role, 'guard_name' => 'web']));
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

        // Email is deliberately NOT accepted here. It used to be, with no
        // proof of ownership and no re-verification, which meant a single
        // leaked bearer token could move the account to an attacker's inbox
        // and then "forget" the password to own it outright. Email changes now
        // go through POST /api/auth/email/change, which demands the current
        // password and only swaps the address once the new one is proved.
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'preferred_language' => ['sometimes', 'string', 'in:sw,en,lg,rw,fr'],
            'avatar' => ['sometimes', ...UploadRules::raster(2048)],
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

    /**
     * Token lifetime, in seconds, taken from config/sanctum.php.
     *
     * A null expiration in Sanctum means "never expire"; we surface that as a
     * long-but-finite window rather than claiming immortality to the client.
     */
    private static function tokenLifetimeSeconds(): int
    {
        return self::cookieLifetimeMinutes() * 60;
    }

    /**
     * Session cookie lifetime, in minutes, matched to the Sanctum expiration
     * so the cookie and the token behind it can never disagree.
     */
    private static function cookieLifetimeMinutes(): int
    {
        $minutes = config('sanctum.expiration');

        return $minutes === null ? 60 * 24 * 30 : (int) $minutes;
    }
}
