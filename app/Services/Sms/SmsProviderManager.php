<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use App\Services\Sms\Providers\AfricasTalkingProvider;
use App\Services\Sms\Providers\LogProvider;
use App\Services\Sms\Providers\TwilioProvider;
use Illuminate\Support\Facades\Log;

/**
 * Builds the SmsProvider named by config('services.sms.provider').
 *
 * Registering a new aggregator is one entry in the match below plus one class:
 * nothing in the authentication, OTP or advisory code changes.
 */
class SmsProviderManager
{
    /** @var array<string, SmsProvider> */
    private array $resolved = [];

    public function driver(?string $name = null): SmsProvider
    {
        $name = strtolower($name ?? (string) config('services.sms.provider', 'africastalking'));

        return $this->resolved[$name] ??= $this->make($name);
    }

    /** @return array<int, string> */
    public function available(): array
    {
        return ['africastalking', 'twilio', 'log'];
    }

    private function make(string $name): SmsProvider
    {
        return match ($name) {
            'africastalking' => new AfricasTalkingProvider(
                username: (string) config('services.africastalking.username', ''),
                apiKey: (string) config('services.africastalking.api_key', ''),
                senderId: (string) config('services.sms.sender_id', 'MKULIMA'),
            ),
            'twilio' => new TwilioProvider(
                sid: (string) config('services.twilio.sid', ''),
                token: (string) config('services.twilio.token', ''),
                from: (string) config('services.twilio.from', ''),
            ),
            'log' => new LogProvider,
            default => $this->fallback($name),
        };
    }

    /**
     * An unknown provider name is a configuration mistake, not a reason to
     * take sign-in down: log it loudly and fall back to the safe local driver
     * so nothing is silently texted through the wrong gateway.
     */
    private function fallback(string $name): SmsProvider
    {
        Log::error('Unknown SMS provider configured; falling back to log driver.', [
            'configured' => $name,
            'available' => $this->available(),
        ]);

        return new LogProvider;
    }
}
