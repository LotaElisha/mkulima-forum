<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $passkey;
    protected string $shortcode;
    protected bool $sandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key') ?? '';
        $this->consumerSecret = config('services.mpesa.consumer_secret') ?? '';
        $this->passkey = config('services.mpesa.passkey') ?? '';
        $this->shortcode = config('services.mpesa.shortcode') ?? '174379';
        $this->sandbox = (bool) (config('services.mpesa.sandbox') ?? true);
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.safaricom.co.ke'
            : 'https://api.safaricom.co.ke';
    }

    /**
     * Get M-Pesa access token
     */
    public function getAccessToken(): ?string
    {
        try {
            $credentials = base64_encode("{$this->consumerKey}:{$this->consumerSecret}");
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
            ])->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('M-Pesa token error', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Initiate STK Push (Lipa na M-Pesa)
     */
    public function stkPush(string $phone, float $amount, string $reference, string $description): array
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token',
            ];
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode("{$this->shortcode}{$this->passkey}{$timestamp}");
        
        // Format phone number
        $phone = $this->formatPhone($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => config('app.url') . '/api/payments/mpesa/callback',
            'AccountReference' => $reference,
            'TransactionDesc' => $description,
        ];

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                    'merchant_request_id' => $data['MerchantRequestID'] ?? null,
                    'response_code' => $data['ResponseCode'] ?? null,
                    'response_description' => $data['ResponseDescription'] ?? null,
                ];
            }

            Log::error('M-Pesa STK push error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'STK push failed',
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa STK push exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query STK Push transaction status
     */
    public function queryTransaction(string $checkoutRequestId): array
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token',
            ];
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode("{$this->shortcode}{$this->passkey}{$timestamp}");

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/mpesa/stkpushquery/v1/query", $payload);

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
     * Format phone number to 2547XXXXXXXX
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Verify M-Pesa callback
     */
    public function verifyCallback(array $data): bool
    {
        // In production, verify the callback using the passkey
        // For now, check basic structure
        return isset($data['Body']['stkCallback']);
    }
}
