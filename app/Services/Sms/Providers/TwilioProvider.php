<?php

namespace App\Services\Sms\Providers;

use App\Contracts\SmsProvider;
use App\Services\Sms\SmsDeliveryResult;
use Illuminate\Support\Facades\Http;

/**
 * Twilio — fallback and international reach.
 *
 * Kept behind the same interface as Africa's Talking so switching gateways
 * during an outage is an env change and a queue restart, not a deployment.
 */
class TwilioProvider implements SmsProvider
{
    public function __construct(
        private readonly string $sid,
        private readonly string $token,
        private readonly string $from,
        private readonly int $timeoutSeconds = 15,
    ) {}

    public function name(): string
    {
        return 'twilio';
    }

    public function isConfigured(): bool
    {
        return $this->sid !== '' && $this->token !== '' && $this->from !== '';
    }

    public function send(string $phone, string $message): SmsDeliveryResult
    {
        try {
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->timeout($this->timeoutSeconds)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                    'To' => $phone,
                    'From' => $this->from,
                    'Body' => $message,
                ]);
        } catch (\Throwable $e) {
            return SmsDeliveryResult::failed($this->name(), $e->getMessage());
        }

        if (! $response->successful()) {
            return SmsDeliveryResult::failed($this->name(), $response->body());
        }

        return SmsDeliveryResult::sent($this->name(), $response->json('sid'));
    }
}
