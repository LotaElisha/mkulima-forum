<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\RegisterFarmer;
use App\Http\Controllers\Api\Auth\PasswordController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(RegisterFarmer $registerFarmer)
    {
        $this->email = mb_strtolower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:users,pending_email'],
            'password' => ['required', 'confirmed', ...PasswordController::passwordRules()],
        ]);

        $key = 'web-register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Umejaribu mara nyingi. Subiri kidogo.');

            return;
        }
        RateLimiter::hit($key, 60);

        $user = DB::transaction(fn () => $registerFarmer($validated));
        $user->sendEmailVerificationNotification();

        Auth::login($user);
        session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
