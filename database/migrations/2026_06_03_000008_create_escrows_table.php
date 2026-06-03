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
            $table->string('reference', 50)->unique();
            $table->enum('status', ['HELD', 'RELEASED', 'DISPUTED', 'REFUNDED', 'FINALIZED', 'ARBITRATED'])->default('HELD');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('provider', 20)->default('mpesa'); // mpesa, tigo_pesa, airtel_money
            $table->string('provider_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
