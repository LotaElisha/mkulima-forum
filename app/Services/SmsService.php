<?php

namespace App\Services;

use App\Contracts\SmsProvider;
use App\Models\SmsLog;
use App\Services\Sms\SmsProviderManager;
use Illuminate\Support\Facades\Log;

/**
 * Application-facing SMS API: normalisation, logging, bulk fan-out and the
 * message templates. Delivery itself is delegated to an App\Contracts\SmsProvider.
 *
 * This class used to embed the Africa's Talking and Twilio HTTP calls directly,
 * duplicated again in App\Services\Notifications\SmsService. Both gateways are
 * now implementations behind one interface, so changing aggregator — likely at
 * some point, given Tanzanian SMS pricing — cannot reach the OTP or login code.
 *
 * The public method signatures and return shapes are unchanged, so every
 * existing caller keeps working.
 */
class SmsService
{
    protected SmsProvider $provider;

    protected string $senderId;

    public function __construct(?SmsProviderManager $manager = null)
    {
        $this->provider = ($manager ?? app(SmsProviderManager::class))->driver();
        $this->senderId = (string) config('services.sms.sender_id', 'MKULIMA');
    }

    /** Which gateway is live, for diagnostics and admin screens. */
    public function gateway(): string
    {
        return $this->provider->name();
    }

    public function isConfigured(): bool
    {
        return $this->provider->isConfigured();
    }

    /**
     * @return array<string, mixed>
     */
    public function send(string $phone, string $message, string $type = 'alert', ?int $userId = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'SMS gateway is not configured'];
        }

        $formatted = $this->formatPhone($phone);

        $log = SmsLog::create([
            'user_id' => $userId,
            'phone' => $formatted,
            'message' => $message,
            'gateway' => $this->provider->name(),
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            $result = $this->provider->send($formatted, $message);
        } catch (\Throwable $e) {
            // A provider should return a failed result rather than throw, but
            // a bug in one gateway must not take the caller down with it.
            Log::error('SMS send failed: '.$e->getMessage(), ['gateway' => $this->provider->name()]);
            $log->update(['status' => 'failed', 'gateway_response' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage(),
            ];
        }

        $payload = $result->toArray();

        $log->update([
            'status' => $result->success ? 'sent' : 'failed',
            'gateway_response' => json_encode($payload),
            'message_id' => $result->messageId,
        ]);

        if (! $result->success) {
            Log::warning('SMS rejected by gateway', [
                'gateway' => $result->provider,
                'error' => $result->error,
            ]);
        }

        return $payload;
    }

    public function sendBulk(array $recipients, string $message, string $type = 'alert'): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? ($recipient['phone'] ?? '') : $recipient;
            $userId = is_array($recipient) ? ($recipient['user_id'] ?? null) : null;
            $results[] = $this->send($phone, $message, $type, $userId);
        }

        return [
            'success' => true,
            'total' => count($results),
            'sent' => count(array_filter($results, fn ($r) => $r['success'])),
            'failed' => count(array_filter($results, fn ($r) => ! $r['success'])),
            'details' => $results,
        ];
    }

    public function sendAdvisory(string $phone, string $crop, string $advisory, ?int $userId = null): array
    {
        $message = "MKULIMA FORUM: Ushauri wa {$crop}\n\n{$advisory}\n\nKwa msaada zaidi piga *384#";

        return $this->send($phone, $message, 'advisory', $userId);
    }

    public function sendWeatherAlert(string $phone, array $weather, ?int $userId = null): array
    {
        $temp = $weather['temperature'] ?? 'N/A';
        $desc = $weather['description'] ?? 'Unknown';
        $location = $weather['location'] ?? 'your area';

        $message = "MKULIMA WEATHER: {$location}\nTemp: {$temp}C, {$desc}\n\n";
        $message .= 'Check app for farming advisory. *384# for IVR.';

        return $this->send($phone, $message, 'alert', $userId);
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '255'.substr($phone, 1);
        }
        if (str_starts_with($phone, '7') || str_starts_with($phone, '6')) {
            $phone = '255'.$phone;
        }

        return '+'.$phone;
    }
}
