<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Email + password check shared by the website and /api/v1.
 *
 * Unknown address, wrong password, and an inactive account all return null.
 * The caller answers with one message, so this cannot be used to learn which
 * addresses are registered.
 */
class AuthenticateEmail
{
    public function __invoke(string $email, string $password): ?User
    {
        $user = User::query()
            ->whereRaw('lower(email) = lower(?)', [$email])
            ->first();

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            return null;
        }

        if ($user->status !== 'active' || $user->is_active === false) {
            return null;
        }

        return $user;
    }
}
