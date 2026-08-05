<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Financial ledger for escrow movements (matches EscrowService usage).
        Schema::create('escrow_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained('escrows')->onDelete('cascade');
            $table->string('entry_type', 20); // hold, deposit, release, refund, fee
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description')->nullable();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['escrow_id', 'entry_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_ledger');
    }
};
