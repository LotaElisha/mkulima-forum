<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;

class StoreContactMessage
{
    public function __invoke(array $input): ContactMessage
    {
        return ContactMessage::query()->create([
            'name' => $input['name'],
            'email' => mb_strtolower($input['email']),
            'phone' => $input['phone'] ?? null,
            'subject' => $input['subject'] ?? null,
            'organization' => $input['organization'] ?? null,
            'message' => $input['message'],
        ]);
    }
}
