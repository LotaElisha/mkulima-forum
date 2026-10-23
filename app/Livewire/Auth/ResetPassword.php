<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\ResetPassword as ResetPasswordAction;
use App\Http\Controllers\Api\Auth\PasswordController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token, string $email = ''): void
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function resetPassword(ResetPasswordAction $reset)
    {
        $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', ...PasswordController::passwordRules()],
        ]);

        $key = 'web-reset:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Umejaribu mara nyingi. Subiri kidogo.');

            return;
        }
        RateLimiter::hit($key, 60);

        $status = $reset($this->email, $this->password, $this->password_confirmation, $this->token);

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', __('auth_flows.reset_failed'));

            return;
        }

        session()->flash('status', __('auth_flows.reset_done'));

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
