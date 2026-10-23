<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

/**
 * Always attempts the send and never reports whether the address exists.
 */
class SendPasswordReset
{
    public function __invoke(string $email): void
    {
        Password::broker()->sendResetLink([
            'email' => mb_strtolower($email),
        ]);
    }
}
