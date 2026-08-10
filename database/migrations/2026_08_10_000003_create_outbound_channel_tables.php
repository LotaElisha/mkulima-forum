<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_messages', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('channel_driver', 32); // push|sms|whatsapp_business|whatsapp_channel_link|telegram|email|in_app_banner|ussd_session
            $table->json('audience_filter')->nullable();
            $table->json('payload'); // {title, body, action_url, metadata}
            $table->string('status', 20)->default('queued'); // queued|processing|sent|failed|cancelled
            $table->integer('recipient_count')->default(0);
            $table->integer('retry_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('delivery_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('outbound_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_identifier', 128)->nullable();
            $table->string('channel_driver', 32);
            $table->string('status', 20); // delivered|failed|read|bounced
            $table->string('external_id', 128)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'status']);
        });

        Schema::create('message_suppression_list', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_identifier', 128)->index(); // phone or email or push token
            $table->string('channel_driver', 32);
            $table->string('reason', 64)->default('user_opt_out');
            $table->timestamps();

            $table->unique(['recipient_identifier', 'channel_driver']);
        });

        Schema::create('channel_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('channel_driver', 32)->unique();
            $table->integer('max_per_minute')->default(60);
            $table->integer('max_per_hour')->default(1000);
            $table->integer('current_minute_count')->default(0);
            $table->integer('current_hour_count')->default(0);
            $table->timestamp('reset_minute_at')->nullable();
            $table->timestamp('reset_hour_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_rate_limits');
        Schema::dropIfExists('message_suppression_list');
        Schema::dropIfExists('delivery_receipts');
        Schema::dropIfExists('outbound_messages');
    }
};
