<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AccountIdentityService;
use App\Services\OtpService;
use App\Services\SmsService;
use App\Services\Spine\ConfigRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Attaching and detaching sign-in identities on an existing account.
 *
 * This is the path that stops one farmer becoming two accounts: rather than
 * discovering a duplicate after the fact, a signed-in user adds their phone
 * number to the account they already have.
 */
class IdentityController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly SmsService $smsService,
        private readonly ConfigRegistry $configRegistry,
        private readonly AccountIdentityService $identities,
    ) {}

    /** Everything currently attached to this account. */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'identities' => $this->identities->identities($request->user()),
        ]);
    }

    /**
     * Send a code to a phone number the signed-in user wants to attach.
     */
    public function requestPhoneLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
        ]);

        $user = $request->user();
        $phone = $validated['phone'];

        if ($user->phone === $phone && $user->phone_verified_at) {
            return response()->json(['message' => __('auth_flows.phone_already_linked')], 422);
        }

        // Fail before sending an SMS we would refuse to act on anyway.
        $owner = User::where('phone', $phone)->first();
        if ($owner && $owner->id !== $user->id) {
            throw ValidationException::withMessages([
                'phone' => [__('auth_flows.phone_taken')],
            ]);
        }

        if (! $this->otpEnabled()) {
            return response()->json(['message' => __('auth_flows.otp_disabled')], 503);
        }

        if ($this->otpService->isRateLimited($phone)) {
            return response()->json(['message' => __('auth_flows.otp_rate_limited')], 429);
        }

        if (app()->environment('production') && ! $this->smsService->isConfigured()) {
            return response()->json(['message' => __('auth_flows.otp_unavailable')], 503);
        }

        $result = $this->otpService->generate($phone, 'link');

        if (! app()->environment('local', 'testing')) {
            $delivery = $this->smsService->send(
                $phone,
                "MkulimaForum verification code: {$result['code']}. It expires in 10 minutes.",
                'otp',
                $user->id
            );
            if (! ($delivery['success'] ?? false)) {
                return response()->json(['message' => __('auth_flows.otp_unavailable')], 503);
            }
        }

        $response = [
            'message' => $result['message'],
            'expires_in' => $result['expires_in'],
        ];

        if (app()->environment('local', 'testing') && config('app.debug')) {
            $response['dev_code'] = $result['code'];
        }

        return response()->json($response);
    }

    /**
     * Verify the code and attach the number.
     *
     * Refuses when the number belongs to another account rather than merging
     * the two — merging moves farm records and wallet balances, and must never
     * be something a stranger with a phone number can trigger.
     */
    public function confirmPhoneLink(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^255[0-9]{9}$/'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $phone = $validated['phone'];

        if (! $this->otpEnabled()) {
            return response()->json(['message' => __('auth_flows.otp_disabled')], 503);
        }

        if ($this->otpService->isVerificationLimited($phone, 'link')) {
            return response()->json(['message' => __('auth_flows.otp_attempts')], 429);
        }

        if (! $this->otpService->verify($phone, $validated['code'], 'link')) {
            return response()->json(['message' => __('auth_flows.otp_invalid')], 422);
        }

        $resolved = $this->identities->resolveByPhone($phone, $user, fn () => $user);

        return response()->json([
            'message' => __('auth_flows.phone_linked'),
            'identities' => $this->identities->identities($resolved->refresh()),
        ]);
    }

    /**
     * Detach the phone number.
     *
     * Requires the account password, and refuses when the phone is the only
     * way in — unlinking the last credential locks the owner out for good.
     */
    public function unlinkPhone(Request $request): JsonResponse
    {
        $request->validate(['current_password' => ['required', 'string']]);
        $user = $request->user();

        if (! $user->phone) {
            return response()->json(['message' => __('auth_flows.no_phone_linked')], 422);
        }

        if (! $user->password || ! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth_flows.current_password_wrong')],
            ]);
        }

        if (! $this->identities->hasAlternativeCredential($user, 'phone')) {
            return response()->json(['message' => __('auth_flows.last_credential')], 422);
        }

        $user->setPrivileged(['phone' => null, 'phone_verified_at' => null]);

        return response()->json([
            'message' => __('auth_flows.phone_unlinked'),
            'identities' => $this->identities->identities($user->refresh()),
        ]);
    }

    private function otpEnabled(): bool
    {
        return (bool) $this->configRegistry->get(
            'auth.otp_enabled',
            ! app()->environment('production')
        );
    }
}
