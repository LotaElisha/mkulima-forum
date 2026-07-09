<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mkulima Bot — persisted AI advisor conversations (multi-turn chat).
     */
    public function up(): void
    {
        Schema::create('bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('title')->nullable(); // first user message, truncated
            $table->string('language', 5)->default('sw');
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('bot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_conversation_id')->constrained('bot_conversations')->onDelete('cascade');
            $table->enum('role', ['user', 'model']);
            $table->text('content');
            $table->json('metadata')->nullable(); // kb sources, weather context, model used
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_messages');
        Schema::dropIfExists('bot_conversations');
    }
};
