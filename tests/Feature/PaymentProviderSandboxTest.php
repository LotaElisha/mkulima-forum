<?php

namespace Tests\Feature;

use App\Services\Payments\MpesaService;
use App\Services\Payments\TigoPesaService;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('payment-sandbox')]
class PaymentProviderSandboxTest extends TestCase
{
    private function requireSandbox(string $provider, array $required): void
    {
        if (! filter_var(env('RUN_PAYMENT_SANDBOX', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set RUN_PAYMENT_SANDBOX=true to exercise real provider sandboxes.');
        }

        foreach ($required as $key) {
            if (! config("services.{$provider}.{$key}")) {
                $this->markTestSkipped("Missing {$provider} sandbox credential: {$key}");
            }
        }
    }

    public function test_real_mpesa_sandbox_accepts_an_stk_push(): void
    {
        $this->requireSandbox('mpesa', ['consumer_key', 'consumer_secret', 'passkey', 'shortcode']);
        config(['services.mpesa.sandbox' => true]);

        $result = app(MpesaService::class)->stkPush(
            env('MPESA_SANDBOX_PHONE', '254708374149'),
            1,
            'MKF-SANDBOX-'.now()->timestamp,
            'MkulimaForum automated sandbox verification'
        );

        $this->assertTrue($result['success'] ?? false, $result['message'] ?? 'M-Pesa sandbox rejected the request.');
        $this->assertNotEmpty($result['checkout_request_id'] ?? null);
    }

    public function test_real_tigopesa_sandbox_authenticates_and_accepts_payment(): void
    {
        $this->requireSandbox('tigopesa', ['api_key', 'api_secret', 'merchant_id']);
        config(['services.tigopesa.sandbox' => true]);

        $result = app(TigoPesaService::class)->pushPayment(
            env('TIGOPESA_SANDBOX_PHONE', '255713000000'),
            1,
            'MKF-SANDBOX-'.now()->timestamp,
            'MkulimaForum automated sandbox verification'
        );

        $this->assertTrue($result['success'] ?? false, $result['message'] ?? 'Tigo Pesa sandbox rejected the request.');
    }
}
