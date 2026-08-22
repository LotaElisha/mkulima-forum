<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Password lifecycle: forgot, reset, change.
 *
 * None of this existed before — an account whose password was forgotten was
 * simply lost, which for a farmer with one email address means losing their
 * farm records, orders and wallet history.
 */
class PasswordController extends Controller
{
    /**
     * Start a reset.
     *
     * Always answers 200 with the same body whether or not the address is
     * registered. Anything else turns this endpoint into an account
     * enumeration oracle: an attacker could harvest which of a list of
     * Tanzanian email addresses hold MkulimaForum accounts.
     */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        Password::broker()->sendResetLink(['email' => strtolower($request->input('email'))]);

        return response()->json([
            'message' => __('auth_flows.reset_link_sent'),
        ]);
    }

    /**
     * Complete a reset.
     *
     * On success every existing API token is revoked: if the reset was needed
     * because the account was compromised, leaving the attacker's session
     * alive would defeat the point.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', ...self::passwordRules()],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // A reset also proves control of the inbox.
                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__('auth_flows.reset_failed')],
            ]);
        }

        return response()->json(['message' => __('auth_flows.reset_done')]);
    }

    /**
     * Change the password of a signed-in account.
     *
     * Requires the current password even though the caller already holds a
     * valid token — a stolen token alone must not be enough to lock the real
     * owner out of their own account.
     */
    public function change(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'different:current_password', ...self::passwordRules()],
        ]);

        if (! $user->password || ! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth_flows.current_password_wrong')],
            ]);
        }

        $user->forceFill(['password' => Hash::make($request->input('password'))])->save();

        // Keep this device signed in, drop every other session.
        $currentId = $user->currentAccessToken()?->id;
        $user->tokens()->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))->delete();

        return response()->json(['message' => __('auth_flows.password_changed')]);
    }

    /**
     * One password policy for the whole platform.
     *
     * 12 characters and an uncompromised-password check, but deliberately no
     * symbol/case gymnastics: on a low-cost Android keyboard those rules push
     * people towards "Password1!" written on a scrap of paper.
     */
    public static function passwordRules(): array
    {
        $rule = PasswordRule::min(12);

        if (app()->environment('production')) {
            $rule = $rule->uncompromised();
        }

        return ['string', $rule];
    }
}
