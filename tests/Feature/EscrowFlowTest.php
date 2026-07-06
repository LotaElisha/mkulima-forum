<?php

namespace Tests\Feature;

use App\Models\Escrow;
use App\Models\EscrowLedger;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Payments\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
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
}
