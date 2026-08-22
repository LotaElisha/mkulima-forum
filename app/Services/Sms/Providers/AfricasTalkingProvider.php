<?php

namespace App\Services\Sms\Providers;

use App\Contracts\SmsProvider;
use App\Services\Sms\SmsDeliveryResult;
use Illuminate\Support\Facades\Http;

/**
 * Africa's Talking — the default gateway for Tanzania and the wider EAC.
 *
 * Chosen as default because it bills in-region, supports Tanzanian sender-ID
 * registration, and has better delivery rates to Vodacom/Airtel/Tigo numbers
 * than a US-routed aggregator.
 */
class AfricasTalkingProvider implements SmsProvider
{
    public function __construct(
        private readonly string $username,
        private readonly string $apiKey,
        private readonly string $senderId,
        private readonly int $timeoutSeconds = 15,
    ) {}

    public function name(): string
    {
        return 'africastalking';
    }

    public function isConfigured(): bool
    {
        return $this->username !== '' && $this->apiKey !== '';
    }

    public function send(string $phone, string $message): SmsDeliveryResult
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])
                // A gateway that hangs must not hold a farmer's sign-in open.
                ->timeout($this->timeoutSeconds)
                ->asForm()
                ->post('https://api.africastalking.com/version1/messaging', [
                    'username' => $this->username,
                    'to' => $phone,
                    'message' => $message,
                    'from' => $this->senderId,
                ]);
        } catch (\Throwable $e) {
            return SmsDeliveryResult::failed($this->name(), $e->getMessage());
        }

        if (! $response->successful()) {
            return SmsDeliveryResult::failed($this->name(), $response->body());
        }

        $recipient = $response->json('SMSMessageData.Recipients.0');

        // A 200 does not mean accepted: per-recipient status carries the real
        // outcome, and a rejected number comes back inside a successful body.
        $status = $recipient['status'] ?? '';
        if ($status !== '' && ! in_array($status, ['Success', 'Sent', 'Queued'], true)) {
            return SmsDeliveryResult::failed($this->name(), $status);
        }

        return SmsDeliveryResult::sent(
            $this->name(),
            $recipient['messageId'] ?? null,
            isset($recipient['cost']) ? (string) $recipient['cost'] : null,
        );
    }
}
