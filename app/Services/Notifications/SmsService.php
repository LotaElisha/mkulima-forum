<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $username;
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = config('services.africastalking.username', '');
        $this->apiKey = config('services.africastalking.api_key', '');
        $this->baseUrl = 'https://api.africastalking.com/version1';
    }

    /**
     * Send SMS via Africa's Talking
     */
    public function send(string $phone, string $message, string $sender = 'MkulimaForum'): array
    {
        if (!$this->username || !$this->apiKey) {
            Log::warning('Africa\'s Talking not configured');
            return ['success' => false, 'message' => 'SMS service not configured'];
        }

        $phone = $this->formatPhone($phone);

        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/messaging", [
                'username' => $this->username,
                'to' => $phone,
                'message' => $message,
                'from' => $sender,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['SMSMessageData']['Recipients'])) {
                $recipient = $result['SMSMessageData']['Recipients'][0];
                return [
                    'success' => true,
                    'message_id' => $recipient['messageId'] ?? null,
                    'status' => $recipient['status'] ?? 'Unknown',
                    'cost' => $recipient['cost'] ?? '0',
                ];
            }

            Log::error('SMS send failed', ['response' => $result]);
            return [
                'success' => false,
                'message' => $result['SMSMessageData']['Message'] ?? 'Failed to send SMS',
            ];
        } catch (\Exception $e) {
            Log::error('SMS exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send OTP via SMS
     */
    public function sendOtp(string $phone, string $code, string $purpose = 'login'): array
    {
        $messages = [
            'login' => "MkulimaForum: Namba yako ya kuthibitisha ni {$code}. Muda wake ni dakika 10.",
            'register' => "Karibu MkulimaForum! Namba yako ya kuthibitisha ni {$code}. Muda wake ni dakika 10.",
            'reset' => "MkulimaForum: Namba yako ya kuweka upya nenosiri ni {$code}. Muda wake ni dakika 10.",
            'payment' => "MkulimaForum: Namba yako ya kuthibitisha malipo ni {$code}. Muda wake ni dakika 10.",
        ];

        $message = $messages[$purpose] ?? "MkulimaForum: Namba yako ya kuthibitisha ni {$code}. Muda wake ni dakika 10.";

        return $this->send($phone, $message);
    }

    /**
     * Send order notification
     */
    public function sendOrderNotification(string $phone, string $orderNumber, string $status): array
    {
        $messages = [
            'placed' => "MkulimaForum: Ombi lako la bidhaa #{$orderNumber} limepokelewa. Tutaendelea kukujulisha.",
            'paid' => "MkulimaForum: Malipo ya ombi #{$orderNumber} yamepokelewa. Bidhaa itatumwa hivi karibuni.",
            'shipped' => "MkulimaForum: Bidhaa yako #{$orderNumber} imetumwa. Asante kwa biashara yetu!",
            'delivered' => "MkulimaForum: Bidhaa yako #{$orderNumber} imefikishwa. Tafadhali thibitisha pokezi.",
        ];

        $message = $messages[$status] ?? "MkulimaForum: Hali ya ombi lako #{$orderNumber} imebadilika hadi {$status}.";

        return $this->send($phone, $message);
    }

    /**
     * Format phone number
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
