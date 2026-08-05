<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $gateway;
    protected string $username;
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->gateway = config('services.sms.gateway', 'africastalking');
        $this->username = config('services.africastalking.username', env('AFRICASTALKING_USERNAME', 'sandbox'));
        $this->apiKey = config('services.africastalking.api_key', env('AFRICASTALKING_API_KEY', ''));
        $this->senderId = config('services.sms.sender_id', 'MKULIMA');
    }

    public function send(string $phone, string $message, string $type = 'alert', ?int $userId = null): array
    {
        $log = SmsLog::create([
            'user_id' => $userId,
            'phone' => $this->formatPhone($phone),
            'message' => $message,
            'gateway' => $this->gateway,
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            if ($this->gateway === 'africastalking') {
                $result = $this->sendViaAfricasTalking($phone, $message);
            } else {
                $result = $this->sendViaTwilio($phone, $message);
            }

            $log->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'gateway_response' => json_encode($result),
                'message_id' => $result['message_id'] ?? null,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS send failed: ' . $e->getMessage());
            $log->update([
                'status' => 'failed',
                'gateway_response' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage(),
            ];
        }
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
            'sent' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success'])),
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
        $message .= "Check app for farming advisory. *384# for IVR.";

        return $this->send($phone, $message, 'alert', $userId);
    }

    protected function sendViaAfricasTalking(string $phone, string $message): array
    {
        $response = Http::withHeaders([
            'apiKey' => $this->apiKey,
            'Accept' => 'application/json',
        ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => $this->username,
            'to' => $this->formatPhone($phone),
            'message' => $message,
            'from' => $this->senderId,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['SMSMessageData']['Recipients'][0]['messageId'] ?? null,
                'cost' => $data['SMSMessageData']['Recipients'][0]['cost'] ?? null,
                'gateway' => 'africastalking',
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
            'gateway' => 'africastalking',
        ];
    }

    protected function sendViaTwilio(string $phone, string $message): array
    {
        $sid = config('services.twilio.sid', env('TWILIO_SID', ''));
        $token = config('services.twilio.token', env('TWILIO_TOKEN', ''));
        $from = config('services.twilio.from', env('TWILIO_FROM', ''));

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $this->formatPhone($phone),
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['sid'] ?? null,
                'gateway' => 'twilio',
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
            'gateway' => 'twilio',
        ];
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        if (str_starts_with($phone, '7') || str_starts_with($phone, '6')) {
            $phone = '255' . $phone;
        }
        return '+' . $phone;
    }
}
