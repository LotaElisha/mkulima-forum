<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content reporting & moderation queue: farmers can flag misleading
     * agricultural advice, fake products and abusive users; admins review.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reportable_type', 32); // forum_thread|forum_reply|product|user
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason', 32); // spam|misleading|fraud|abuse|counterfeit|other
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending'); // pending|resolved|dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution_action', 32)->nullable(); // none|content_hidden|listing_disabled
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
            // "One open report per user per target" is enforced in the
            // controller — a unique index over status would break once the
            // same user legitimately re-reports previously-resolved content.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
