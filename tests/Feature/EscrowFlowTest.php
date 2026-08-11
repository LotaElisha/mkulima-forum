<?php

namespace Tests\Feature;

use App\Models\EscrowLedger;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EscrowFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(): Order
    {
        $tenant = Tenant::create([
            'name' => 'Tanzania',
            'country_code' => 'tz',
            'currency' => 'TZS',
        ]);

        $buyer = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mnunuzi Mfano',
            'phone' => '255700000001',
            'role' => 'farmer',
        ]);

        $seller = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Muuzaji Mfano',
            'phone' => '255700000002',
            'role' => 'agrodealer',
        ]);

        return Order::create([
            'tenant_id' => $tenant->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'uuid' => (string) Str::uuid(),
            'status' => 'pending',
            'subtotal' => 50000,
            'delivery_fee' => 5000,
            'total' => 55000,
            'currency' => 'TZS',
            'delivery_address' => ['region' => 'Dar es Salaam', 'street' => 'Kariakoo'],
            'delivery_phone' => '255700000001',
        ]);
    }

    public function test_escrow_is_created_pending_with_hold_ledger_entry(): void
    {
        $order = $this->makeOrder();
        $service = app(EscrowService::class);

        $escrow = $service->createEscrow($order, 'mpesa');

        $this->assertSame('pending', $escrow->status);
        $this->assertEquals(55000, (float) $escrow->amount);
        $this->assertSame(1, EscrowLedger::where('escrow_id', $escrow->id)->count());
    }

    public function test_successful_callback_marks_escrow_held_and_order_paid(): void
    {
        $order = $this->makeOrder();
        $service = app(EscrowService::class);
        $escrow = $service->createEscrow($order, 'mpesa');
        $escrow->update(['transaction_reference' => 'ws_CO_TEST123']);

        $service->handleMpesaCallback([
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => 'ws_CO_TEST123',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 55000],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => 'TESTRECEIPT1'],
                ]],
            ]],
        ]);

        $this->assertSame('held', $escrow->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_duplicate_callback_is_idempotent(): void
    {
        $order = $this->makeOrder();
        $service = app(EscrowService::class);
        $escrow = $service->createEscrow($order, 'mpesa');
        $escrow->update(['transaction_reference' => 'ws_CO_DUP1']);

        $payload = [
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => 'ws_CO_DUP1',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 55000],
                    ['Name' => 'MpesaReceiptNumber', 'Value' => 'TESTRECEIPT2'],
                ]],
            ]],
        ];

        $service->handleMpesaCallback($payload);
        $service->handleMpesaCallback($payload); // gateway retry

        $deposits = EscrowLedger::where('escrow_id', $escrow->id)
            ->where('entry_type', 'deposit')
            ->count();

        $this->assertSame(1, $deposits, 'Duplicate callback must not double-post the deposit.');
        $this->assertSame('held', $escrow->fresh()->status);
    }

    public function test_failed_callback_marks_escrow_failed(): void
    {
        $order = $this->makeOrder();
        $service = app(EscrowService::class);
        $escrow = $service->createEscrow($order, 'mpesa');
        $escrow->update(['transaction_reference' => 'ws_CO_FAIL1']);

        $service->handleMpesaCallback([
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => 'ws_CO_FAIL1',
                'ResultCode' => 1032,
                'ResultDesc' => 'Request cancelled by user',
            ]],
        ]);

        $fresh = $escrow->fresh();
        $this->assertSame('failed', $fresh->status);
        $this->assertSame('Request cancelled by user', $fresh->failure_reason);
    }

    public function test_success_callback_with_wrong_amount_is_rejected(): void
    {
        $order = $this->makeOrder();
        $service = app(EscrowService::class);
        $escrow = $service->createEscrow($order, 'mpesa');
        $escrow->update(['transaction_reference' => 'ws_CO_WRONG_AMOUNT']);

        $service->handleMpesaCallback([
            'Body' => ['stkCallback' => [
                'CheckoutRequestID' => 'ws_CO_WRONG_AMOUNT',
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'CallbackMetadata' => ['Item' => [
                    ['Name' => 'Amount', 'Value' => 1],
                ]],
            ]],
        ]);

        $this->assertSame('failed', $escrow->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_callback_endpoint_rejects_missing_secret(): void
    {
        config(['services.mpesa.callback_secret' => 'test-callback-secret']);

        $this->postJson('/api/payments/mpesa/callback', [
            'Body' => ['stkCallback' => ['CheckoutRequestID' => 'unknown', 'ResultCode' => 0]],
        ])->assertUnauthorized();
    }

    public function test_callback_endpoint_accepts_matching_secret_and_amount(): void
    {
        config(['services.mpesa.callback_secret' => 'test-callback-secret']);
        $order = $this->makeOrder();
        $escrow = app(EscrowService::class)->createEscrow($order, 'mpesa');
        $escrow->update(['transaction_reference' => 'ws_CO_SIGNED']);

        $this->withHeader('X-Mkulima-Webhook-Secret', 'test-callback-secret')
            ->postJson('/api/payments/mpesa/callback', [
                'Body' => ['stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_SIGNED',
                    'ResultCode' => 0,
                    'ResultDesc' => 'Success',
                    'CallbackMetadata' => ['Item' => [
                        ['Name' => 'Amount', 'Value' => 55000],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'SIGNED123'],
                    ]],
                ]],
            ])->assertOk();

        $this->assertSame('held', $escrow->fresh()->status);
        $this->assertSame('SIGNED123', $escrow->fresh()->provider_reference);
    }

    public function test_held_escrow_can_be_released_once(): void
    {
        $order = $this->makeOrder();
        $escrow = app(EscrowService::class)->createEscrow($order, 'mpesa');
        $escrow->update(['status' => 'held']);

        $first = app(EscrowService::class)->releaseFunds($escrow->fresh());
        $second = app(EscrowService::class)->releaseFunds($escrow->fresh());

        $this->assertTrue($first['success']);
        $this->assertFalse($second['success']);
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame(1, EscrowLedger::where('escrow_id', $escrow->id)->where('entry_type', 'release')->count());
    }

    public function test_held_escrow_can_be_refunded_once(): void
    {
        $order = $this->makeOrder();
        $escrow = app(EscrowService::class)->createEscrow($order, 'mpesa');
        $escrow->update(['status' => 'held']);

        $first = app(EscrowService::class)->refundBuyer($escrow->fresh(), 'Sandbox lifecycle test');
        $second = app(EscrowService::class)->refundBuyer($escrow->fresh(), 'Duplicate');

        $this->assertTrue($first['success']);
        $this->assertFalse($second['success']);
        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame(1, EscrowLedger::where('escrow_id', $escrow->id)->where('entry_type', 'refund')->count());
    }

    public function test_payment_initiation_is_idempotent_per_order(): void
    {
        config([
            'services.mpesa.consumer_key' => 'sandbox-key',
            'services.mpesa.consumer_secret' => 'sandbox-secret',
            'services.mpesa.passkey' => 'sandbox-passkey',
            'services.mpesa.shortcode' => '174379',
            'services.mpesa.sandbox' => true,
        ]);
        Http::fake([
            'https://sandbox.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'sandbox-token']),
            'https://sandbox.safaricom.co.ke/mpesa/stkpush/*' => Http::response([
                'CheckoutRequestID' => 'ws_CO_IDEMPOTENT',
                'MerchantRequestID' => 'merchant-test',
                'ResponseCode' => '0',
            ]),
        ]);

        $order = $this->makeOrder();
        Sanctum::actingAs($order->buyer);
        $payload = ['order_id' => $order->id, 'payment_method' => 'mpesa', 'phone' => '255700000001'];

        $this->postJson('/api/payments/initiate', $payload)->assertOk();
        $this->postJson('/api/payments/initiate', $payload)->assertStatus(409);

        $this->assertDatabaseCount('escrows', 1);
        Http::assertSentCount(2); // one OAuth request and one STK push only
    }
}
