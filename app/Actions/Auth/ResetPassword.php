<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPassword
{
    /**
     * @return string One of the Password broker status constants.
     */
    public function __invoke(string $email, string $password, string $passwordConfirmation, string $token): string
    {
        return Password::broker()->reset(
            [
                'email' => mb_strtolower($email),
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
                'token' => $token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                if (! $user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );
    }
}
