<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    private User $sender;
    private User $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'name' => 'Tanzania', 'country_code' => 'tz', 'currency' => 'TZS',
        ]);

        $this->sender = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Mtumaji',
            'phone' => '255710000001', 'role' => 'farmer',
        ]);
        $this->recipient = User::create([
            'tenant_id' => $tenant->id, 'name' => 'Mpokeaji',
            'phone' => '255710000002', 'role' => 'farmer',
        ]);
    }

    public function test_balance_endpoint_creates_wallet_lazily(): void
    {
        Sanctum::actingAs($this->sender);
        $this->getJson('/api/wallet/balance')
            ->assertOk()
            ->assertJson(['currency' => 'TZS', 'status' => 'active']);
        $this->assertDatabaseHas('wallets', ['user_id' => $this->sender->id]);
    }

    public function test_deposit_and_withdraw_in_sandbox(): void
    {
        Sanctum::actingAs($this->sender);
        $this->postJson('/api/wallet/deposit', [
            'amount' => 50000, 'phone' => '255710000001', 'provider' => 'mpesa',
        ])->assertOk()->assertJsonPath('new_balance', '50000.00');

        $this->postJson('/api/wallet/withdraw', [
            'amount' => 20000, 'phone' => '255710000001', 'provider' => 'mpesa',
        ])->assertOk()->assertJsonPath('new_balance', '30000.00');

        // Overdraw rejected
        $this->postJson('/api/wallet/withdraw', [
            'amount' => 999999, 'phone' => '255710000001', 'provider' => 'mpesa',
        ])->assertStatus(422);
    }

    public function test_transfer_moves_money_and_writes_double_entry(): void
    {
        Sanctum::actingAs($this->sender);
        $this->postJson('/api/wallet/deposit', [
            'amount' => 10000, 'phone' => '255710000001', 'provider' => 'mpesa',
        ])->assertOk();

        $this->postJson('/api/wallet/transfer', [
            'recipient_phone' => '255710000002', 'amount' => 4000,
        ])->assertOk()->assertJsonPath('new_balance', '6000.00');

        $recipientWallet = Wallet::where('user_id', $this->recipient->id)->first();
        $this->assertEquals(4000, (float) $recipientWallet->balance);

        // Double-entry: transfer_out on sender, transfer_in on recipient
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->sender->id, 'type' => 'transfer_out', 'amount' => -4000,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->recipient->id, 'type' => 'transfer_in', 'amount' => 4000,
        ]);
    }

    public function test_transfer_rejects_insufficient_balance_and_self_transfer(): void
    {
        Sanctum::actingAs($this->sender);

        $this->postJson('/api/wallet/transfer', [
            'recipient_phone' => '255710000002', 'amount' => 5000,
        ])->assertStatus(422); // empty wallet

        $this->postJson('/api/wallet/deposit', [
            'amount' => 10000, 'phone' => '255710000001', 'provider' => 'mpesa',
        ]);

        $this->postJson('/api/wallet/transfer', [
            'recipient_phone' => '255710000001', 'amount' => 1000,
        ])->assertStatus(422); // self-transfer

        $this->postJson('/api/wallet/transfer', [
            'recipient_phone' => '255799999999', 'amount' => 1000,
        ])->assertStatus(404); // unknown recipient
    }
}
