<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Community Channels & Social Links Registry
        Schema::create('community_channels', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('platform', 32); // facebook|instagram|x_twitter|tiktok|youtube|linkedin|threads|telegram|whatsapp|whatsapp_channel|whatsapp_group|whatsapp_community|custom
            $table->string('channel_type', 32); // WHATSAPP_BUSINESS|WHATSAPP_CHANNEL|WHATSAPP_GROUP|WHATSAPP_COMMUNITY|SOCIAL|CUSTOM
            $table->string('name');
            $table->string('slug', 64)->unique();
            $table->json('description')->nullable(); // {sw: "...", en: "..."}
            $table->text('url')->nullable();
            $table->string('phone_number', 32)->nullable();
            $table->json('default_greeting')->nullable(); // {sw: "...", en: "..."}
            $table->string('icon', 64)->default('chat');
            $table->string('language', 10)->default('sw');
            $table->foreignId('geo_unit_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->foreignId('crop_id')->nullable()->constrained('crops')->nullOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('agricultural_topics')->nullOnDelete();
            $table->boolean('is_official')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_alert_channel')->default(false); // Part C: Target for urgent advisories/recalls
            $table->integer('sort_order')->default(0);
            $table->string('provenance', 20)->default('PLATFORM'); // REGULATORY|PLATFORM|AI|COMMUNITY
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['platform', 'is_active']);
            $table->index(['channel_type', 'is_active']);
        });

        // Community Channel Clicks & Engagement
        Schema::create('community_channel_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('community_channels')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('anon_id', 64)->nullable();
            $table->string('event', 32)->default('join_link_clicked'); // channel_view|join_link_clicked|whatsapp_contact_clicked|social_platform_clicked
            $table->string('referrer')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['channel_id', 'event']);
        });

        // Community Channel Moderators
        Schema::create('community_channel_moderators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('community_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->default('moderator'); // admin|moderator
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['channel_id', 'user_id']);
        });

        // Forum Content Reports (In-App Community Safety)
        Schema::create('forum_content_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reportable_type', 64);
            $table->unsignedBigInteger('reportable_id');
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status', 20)->default('pending'); // pending|reviewed|dismissed|actioned
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_content_reports');
        Schema::dropIfExists('community_channel_moderators');
        Schema::dropIfExists('community_channel_clicks');
        Schema::dropIfExists('community_channels');
    }
};
