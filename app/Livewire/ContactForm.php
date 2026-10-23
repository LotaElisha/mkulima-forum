<?php

namespace App\Livewire;

use App\Actions\Contact\StoreContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $organization = '';

    public string $message = '';

    public bool $sent = false;

    public function submit(StoreContactMessage $store): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'subject' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'Umetuma ujumbe mwingi. Jaribu tena baadaye.');

            return;
        }
        RateLimiter::hit($key, 600);

        $store([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'subject' => $validated['subject'],
            'organization' => $validated['organization'] ?: null,
            'message' => $validated['message'],
        ]);

        $this->reset(['name', 'email', 'phone', 'subject', 'organization', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
