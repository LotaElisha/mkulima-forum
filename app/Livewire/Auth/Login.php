<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\AuthenticateEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public ?string $notice = null;

    public function mount(): void
    {
        $this->notice = match (request()->query('verified')) {
            'success' => 'Barua pepe imethibitishwa. Unaweza kuingia sasa.',
            'already' => 'Barua pepe ilikuwa imethibitishwa tayari.',
            'invalid' => 'Kiungo si sahihi au kimeisha muda.',
            'taken' => 'Barua pepe hiyo inatumiwa na akaunti nyingine.',
            'email-changed' => 'Barua pepe mpya imethibitishwa. Ingia tena.',
            default => null,
        };
    }

    public function login(AuthenticateEmail $authenticate)
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'web-login:'.mb_strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('email', 'Umejaribu mara nyingi. Subiri dakika moja.');

            return;
        }

        $user = $authenticate($this->email, $this->password);

        if (! $user) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'Barua pepe au nenosiri si sahihi.');

            return;
        }

        RateLimiter::clear($key);
        Auth::login($user, $this->remember);
        session()->regenerate();

        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
