<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrow_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained('escrows')->onDelete('cascade');
            $table->string('from_status', 20);
            $table->string('to_status', 20);
            $table->foreignId('triggered_by')->constrained('users')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_ledger');
    }
};
