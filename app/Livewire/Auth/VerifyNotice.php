<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VerifyNotice extends Component
{
    public bool $sent = false;

    public function resend(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->addError('email', 'Ingia kwanza ili kutuma kiungo kipya.');

            return;
        }

        $key = 'web-verify:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Umeomba mara nyingi. Subiri dakika chache.');

            return;
        }
        RateLimiter::hit($key, 600);

        if ($user->email && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.verify-notice');
    }
}
