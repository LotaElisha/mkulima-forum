<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel_driver', 32); // push|sms|whatsapp|email
            $table->string('topic_type', 32); // counterfeit_alert|recall|advisory|community|market_price
            $table->boolean('is_opted_in')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel_driver', 'topic_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
