<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Email ownership: verify, resend, and change-with-proof.
 *
 * Before this, registration handed out a full-scope token with
 * email_verified_at left null forever, so "verified email" was a column
 * nothing ever wrote to and password recovery could be pointed at a typo'd
 * address the user never controlled.
 */
class EmailVerificationController extends Controller
{
    /**
     * Land the signed link.
     *
     * Deliberately a web route, not an API one: the link is opened from a mail
     * client, so it has to answer with a page a human can read. The signature
     * middleware has already proved the URL was not edited or replayed.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user) {
            return redirect('/login?verified=invalid');
        }

        // A pending change verifies the staged address; otherwise the address
        // already on the account.
        if ($user->pending_email && hash_equals(sha1($user->pending_email), $hash)) {
            // Someone else may have claimed the address in the meantime.
            $taken = User::where('email', $user->pending_email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($taken) {
                $user->forceFill(['pending_email' => null, 'pending_email_requested_at' => null])->save();

                return redirect('/login?verified=taken');
            }

            $user->forceFill([
                'email' => $user->pending_email,
                'pending_email' => null,
                'pending_email_requested_at' => null,
                'email_verified_at' => now(),
            ])->save();

            event(new Verified($user));

            return redirect('/login?verified=email-changed');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect('/login?verified=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/login?verified=already');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect('/login?verified=success');
    }

    /**
     * Resend the link for the signed-in account.
     *
     * Throttled at the route so a bored client cannot use us to mail-bomb an
     * address, and answers the same way whether or not there was anything to
     * send.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->pending_email) {
            $user->sendPendingEmailVerificationNotification();
        } elseif ($user->email && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json(['message' => __('auth_flows.verification_sent')]);
    }

    /**
     * Current verification state, for the app to decide what to nudge.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
            'pending_email' => $user->pending_email,
            'phone' => $user->phone,
            'phone_verified' => $user->phone_verified_at !== null,
        ]);
    }

    /**
     * Stage an email change.
     *
     * The account keeps its current address until the new one is proved, and
     * the current password is required — so a leaked token cannot walk the
     * account over to an attacker's inbox and then use "forgot password" to
     * take it permanently. This is the escalation path that
     * `PUT /api/auth/profile` used to leave wide open.
     */
    public function requestChange(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:users,pending_email'],
            'current_password' => ['required', 'string'],
        ]);

        if (! $user->password) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth_flows.set_password_first')],
            ]);
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth_flows.current_password_wrong')],
            ]);
        }

        $user->forceFill([
            'pending_email' => strtolower($validated['email']),
            'pending_email_requested_at' => now(),
        ])->save();

        $user->sendPendingEmailVerificationNotification();

        return response()->json([
            'message' => __('auth_flows.email_change_pending'),
            'pending_email' => $user->pending_email,
        ]);
    }

    /**
     * Abandon a staged change.
     */
    public function cancelChange(Request $request): JsonResponse
    {
        $request->user()->forceFill([
            'pending_email' => null,
            'pending_email_requested_at' => null,
        ])->save();

        return response()->json(['message' => __('auth_flows.email_change_cancelled')]);
    }
}
