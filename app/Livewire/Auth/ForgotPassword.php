<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendPasswordReset;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function send(SendPasswordReset $send): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $key = 'web-forgot:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Umeomba mara nyingi. Subiri dakika chache.');

            return;
        }
        RateLimiter::hit($key, 60);

        $send($this->email);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
