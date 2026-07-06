<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ivr_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('phone');
            $table->string('input')->nullable();
            $table->string('action');
            $table->string('status')->nullable();
            $table->integer('duration')->default(0);
            $table->timestamps();

            $table->index('session_id');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ivr_logs');
    }
};
