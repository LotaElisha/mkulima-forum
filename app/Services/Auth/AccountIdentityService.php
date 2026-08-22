<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Keeps one person to one account across email and phone sign-in.
 *
 * The problem this solves: OTP registration keyed on phone alone. A farmer who
 * signed up with an email address in January and later signed in with their
 * phone in March got a second, empty account — different farm records,
 * different orders, different wallet. Nothing in the product could tell the two
 * apart, and the longer the platform ran the more expensive the mess became.
 *
 * The rules, in order of preference:
 *
 *  1. If the identity is already on an account, that is the account. Always.
 *  2. If someone is signed in and the identity is unclaimed, attach it to the
 *     account they are already using. This is the path that actually prevents
 *     duplicates, because the user has told us who they are.
 *  3. If the identity belongs to a DIFFERENT account, refuse and say so.
 *     Never merge automatically: merging two accounts silently moves farm
 *     records and wallet balances between them, and an attacker who controls
 *     one phone number should not be able to trigger that.
 *  4. Only when nothing matches and nobody is signed in do we create.
 */
class AccountIdentityService
{
    /**
     * Resolve a verified phone number to an account.
     *
     * @param  User|null  $currentUser  The signed-in account, if any.
     * @param  callable():User  $createNew  Called only when a new account is
     *                                      genuinely required.
     */
    public function resolveByPhone(string $phone, ?User $currentUser, callable $createNew): User
    {
        $owner = User::where('phone', $phone)->first();

        // Rule 1 — the number already belongs to someone.
        if ($owner) {
            // Rule 3 — and it is not the person holding this session.
            if ($currentUser && $currentUser->id !== $owner->id) {
                throw ValidationException::withMessages([
                    'phone' => [__('auth_flows.phone_taken')],
                ]);
            }

            $owner->setPrivileged(['phone_verified_at' => now()]);

            return $owner;
        }

        // Rule 2 — attach the unclaimed number to the account in hand.
        if ($currentUser) {
            $currentUser->setPrivileged([
                'phone' => $phone,
                'phone_verified_at' => now(),
            ]);

            return $currentUser;
        }

        // Rule 4.
        return $createNew();
    }

    /**
     * Whether this account still has a way in if the given identity is removed.
     *
     * Unlinking the only credential on an account locks the owner out
     * permanently, so both unlink paths check this first.
     */
    public function hasAlternativeCredential(User $user, string $removing): bool
    {
        $hasEmailLogin = $user->email !== null && $user->password !== null;
        $hasPhoneLogin = $user->phone !== null;
        $hasSocialLogin = $user->socialAccounts()->exists();

        return match ($removing) {
            'phone' => $hasEmailLogin || $hasSocialLogin,
            'email' => $hasPhoneLogin || $hasSocialLogin,
            default => false,
        };
    }

    /**
     * A plain description of every way into this account.
     *
     * Surfaced to the app so a farmer can see what is attached without having
     * to work it out from separate fields.
     *
     * @return array<string, mixed>
     */
    public function identities(User $user): array
    {
        return [
            'email' => [
                'value' => $user->email,
                'verified' => $user->hasVerifiedEmail(),
                'pending' => $user->pending_email,
                'can_unlink' => $user->email !== null
                    && $this->hasAlternativeCredential($user, 'email'),
            ],
            'phone' => [
                'value' => $user->phone,
                'verified' => $user->phone_verified_at !== null,
                'can_unlink' => $user->phone !== null
                    && $this->hasAlternativeCredential($user, 'phone'),
            ],
            'social' => $user->socialAccounts()
                ->get(['provider', 'email'])
                ->map(fn ($a) => ['provider' => $a->provider, 'email' => $a->email])
                ->all(),
        ];
    }
}
