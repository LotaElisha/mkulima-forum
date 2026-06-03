<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('title');
            $table->text('content');
            $table->string('source')->default('tari'); // tari, fao, kephis, custom
            $table->string('category')->nullable();
            $table->string('language', 5)->default('sw');
            $table->json('metadata')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'source']);
            $table->index(['tenant_id', 'language']);
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_documents');
    }
};
