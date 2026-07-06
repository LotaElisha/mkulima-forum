<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('forum_category_id')->constrained('forum_categories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('title');
            $table->text('body');
            $table->json('media')->nullable(); // images, voice notes
            $table->string('language', 5)->default('sw');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_verified_answer')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->integer('upvote_count')->default(0);
            $table->enum('status', ['active', 'hidden', 'flagged'])->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'forum_category_id']);
            $table->index(['tenant_id', 'status']);
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
                $table->fullText(['title', 'body']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
    }
};
