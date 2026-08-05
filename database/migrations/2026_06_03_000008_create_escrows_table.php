<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('uuid', 36)->unique();
            $table->string('reference', 50)->nullable()->unique();
            // Statuses match EscrowService transitions (lowercase).
            $table->enum('status', [
                'pending', 'held', 'released', 'disputed',
                'refunded', 'failed', 'finalized', 'arbitrated',
            ])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('payment_method', 20)->default('mpesa'); // mpesa, tigopesa, airtel_money
            $table->string('provider_reference')->nullable();
            $table->string('transaction_reference')->nullable()->index(); // gateway CheckoutRequestID
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('hold_until')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
