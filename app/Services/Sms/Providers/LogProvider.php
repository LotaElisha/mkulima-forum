<?php

namespace App\Services\Sms\Providers;

use App\Contracts\SmsProvider;
use App\Services\Sms\SmsDeliveryResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Writes messages to the log instead of sending them.
 *
 * Used by local development and the test suite so nobody burns SMS credit — or
 * texts a real Tanzanian number — while working on the sign-in flow. It reports
 * itself as configured so OTP paths stay exercisable offline.
 */
class LogProvider implements SmsProvider
{
    public function name(): string
    {
        return 'log';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function send(string $phone, string $message): SmsDeliveryResult
    {
        Log::info('SMS (log provider, not delivered)', [
            'to' => $phone,
            'message' => $message,
        ]);

        return SmsDeliveryResult::sent($this->name(), (string) Str::uuid());
    }
}
