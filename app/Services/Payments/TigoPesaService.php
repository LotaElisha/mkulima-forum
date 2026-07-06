<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TigoPesaService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $merchantId;
    protected bool $sandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tigopesa.api_key') ?? '';
        $this->apiSecret = config('services.tigopesa.api_secret') ?? '';
        $this->merchantId = config('services.tigopesa.merchant_id') ?? '';
        $this->sandbox = (bool) (config('services.tigopesa.sandbox') ?? true);
        $this->baseUrl = $this->sandbox
            ? 'https://openapiuat.tigo.co.tz'
            : 'https://openapi.tigo.co.tz';
    }

    /**
     * Get Tigo Pesa access token
     */
    public function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post("{$this->baseUrl}/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Tigo Pesa token error', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Tigo Pesa token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Initiate push payment (MNO Checkout)
     */
    public function pushPayment(string $phone, float $amount, string $reference, string $description): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token',
            ];
        }

        $phone = $this->formatPhone($phone);

        $payload = [
            'CustomerMSISDN' => $phone,
            'BillerMSISDN' => $this->merchantId,
            'Amount' => $amount,
            'Remarks' => $description,
            'ReferenceID' => $reference,
        ];

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/mno-checkout", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Tigo Pesa push error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Push payment failed',
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Tigo Pesa push exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query transaction status
     */
    public function queryTransaction(string $referenceId): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/transaction/{$referenceId}");

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number to 2557XXXXXXXX
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }

        return $phone;
    }
}
