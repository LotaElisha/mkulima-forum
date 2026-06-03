<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('forum_replies')->onDelete('cascade');
            $table->text('body');
            $table->json('media')->nullable();
            $table->boolean('is_expert_answer')->default(false);
            $table->integer('upvote_count')->default(0);
            $table->enum('status', ['active', 'hidden', 'flagged'])->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'forum_thread_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_replies');
    }
};
