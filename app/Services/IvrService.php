<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class IvrService
{
    protected string $baseUrl;
    protected string $username;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.africastalking.com';
        $this->username = config('services.africastalking.username', env('AFRICASTALKING_USERNAME', 'sandbox'));
        $this->apiKey = config('services.africastalking.api_key', env('AFRICASTALKING_API_KEY', ''));
    }

    public function generateWelcomeResponse(): array
    {
        return [
            'action' => 'Say',
            'text' => 'Karibu Mkulima Forum. Chagua moja. Moja, bei za soko. Mbili, hali ya hewa. Tatu, ongea na mtaalamu. Sufuri, kuacha.',
            'voice' => 'female',
            'language' => 'sw',
        ];
    }

    public function handleMenu(string $dtmf, ?string $sessionId = null): array
    {
        switch ($dtmf) {
            case '1':
                return $this->getPriceCheckMenu();
            case '2':
                return $this->getWeatherMenu();
            case '3':
                return $this->getAgronomistMenu();
            case '0':
                return [
                    'action' => 'Say',
                    'text' => 'Asante kwa kutumia Mkulima Forum. Kwa heri.',
                    'voice' => 'female',
                    'language' => 'sw',
                ];
            default:
                return [
                    'action' => 'Say',
                    'text' => 'Chaguo sio sahihi. Tafadhali jaribu tena.',
                    'voice' => 'female',
                    'language' => 'sw',
                ];
        }
    }

    public function getPriceCheckMenu(): array
    {
        // Mock price data - in production this would fetch from marketplace
        $prices = [
            ['crop' => 'Mahindi', 'price' => '45,000 TZS kwa gunia'],
            ['crop' => 'Mchele', 'price' => '120,000 TZS kwa gunia'],
            ['crop' => 'Maharage', 'price' => '80,000 TZS kwa gunia'],
            ['crop' => 'Viazi', 'price' => '60,000 TZS kwa kisheni'],
        ];

        $text = 'Bei za soko leo. ';
        foreach ($prices as $p) {
            $text .= $p['crop'] . ' ' . $p['price'] . '. ';
        }
        $text .= 'Rudia kupiga moja. Kurudi nyuma piga nyota. Kuacha piga sufuri.';

        return [
            'action' => 'Say',
            'text' => $text,
            'voice' => 'female',
            'language' => 'sw',
        ];
    }

    public function getWeatherMenu(): array
    {
        $weatherService = app(WeatherService::class);
        $weather = $weatherService->getCurrentWeather('Dar es Salaam');
        $advisories = $weatherService->getFarmingAdvisory($weather);

        $text = 'Hali ya hewa leo. ' . $weather['location'] . '. ';
        $text .= 'Joto la digrii ' . $weather['temperature'] . '. ';
        $text .= $weather['description'] . '. ';

        if (!empty($advisories)) {
            $text .= 'Ushauri. ' . $advisories[0]['message'] . '. ';
        }

        $text .= 'Rudia kupiga mbili. Kurudi nyuma piga nyota. Kuacha piga sufuri.';

        return [
            'action' => 'Say',
            'text' => $text,
            'voice' => 'female',
            'language' => 'sw',
        ];
    }

    public function getAgronomistMenu(): array
    {
        return [
            'action' => 'Say',
            'text' => 'Mtaalamu wetu wa kilimo atakupigia simu katika muda wa masaa 24. Tafadhali weka ujumbe baada ya mlio. Bonyeza moja kumaliza.',
            'voice' => 'female',
            'language' => 'sw',
        ];
    }

    public function getVoiceCallbackResponse(array $params): array
    {
        $isActive = $params['isActive'] ?? '1';
        $sessionId = $params['sessionId'] ?? '';
        $direction = $params['direction'] ?? 'Inbound';
        $callerNumber = $params['callerNumber'] ?? '';
        $destinationNumber = $params['destinationNumber'] ?? '';
        $dtmfDigits = $params['dtmfDigits'] ?? null;

        Log::info('IVR callback', [
            'session_id' => $sessionId,
            'caller' => $callerNumber,
            'dtmf' => $dtmfDigits,
        ]);

        if ($isActive === '0') {
            // Call ended
            return ['action' => 'Reject'];
        }

        // First call or no DTMF - play welcome
        if (empty($dtmfDigits)) {
            return [
                'action' => 'Say',
                'text' => 'Karibu Mkulima Forum. Chagua moja. Moja, bei za soko. Mbili, hali ya hewa. Tatu, ongea na mtaalamu. Sufuri, kuacha.',
                'voice' => 'female',
                'language' => 'sw',
            ];
        }

        return $this->handleMenu($dtmfDigits, $sessionId);
    }

    public function makeOutboundCall(string $phone, ?string $message = null): array
    {
        try {
            $xml = $this->buildCallXml($message ?? 'Habari, hii ni simu kutoka Mkulima Forum.');

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post("{$this->baseUrl}/version1/call", [
                'username' => $this->username,
                'to' => $this->formatPhone($phone),
                'from' => config('services.sms.sender_id', 'MKULIMA'),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('IVR call failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function buildCallXml(string $text): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response>'
            . '<Say voice="female" language="sw">' . htmlspecialchars($text) . '</Say>'
            . '</Response>';
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
