<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->uuid('uuid')->unique();
            $table->string('phone');
            $table->text('message');
            $table->string('gateway')->default('africastalking'); // africastalking, twilio
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->string('gateway_response')->nullable();
            $table->string('message_id')->nullable();
            $table->string('type')->default('alert'); // alert, otp, marketing, advisory
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
