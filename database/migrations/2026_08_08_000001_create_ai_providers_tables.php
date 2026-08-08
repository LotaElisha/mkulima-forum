<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('tenant_id')->default(1)->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('provider_type'); // gemini, openai, kimi, claude, deepseek, groq, openrouter, custom
            $table->string('base_url')->nullable();
            $table->string('model')->default('gemini-2.0-flash');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->float('temperature')->nullable()->default(0.7);
            $table->integer('max_tokens')->nullable()->default(2048);
            $table->integer('timeout')->nullable()->default(30);
            $table->string('organization_id')->nullable();
            $table->string('project_id')->nullable();
            $table->integer('rate_limit')->nullable();
            $table->json('additional_config')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_connection_status')->nullable();
            $table->text('last_connection_error')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'is_default']);
        });

        Schema::create('ai_provider_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->text('encrypted_api_key');
            $table->string('key_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('ai_feature_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->default(1)->constrained('tenants')->onDelete('cascade');
            $table->string('feature_key')->unique();
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->onDelete('set null');
            $table->string('model_override')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->default(1)->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->onDelete('set null');
            $table->string('feature');
            $table->string('provider_type');
            $table->string('model');
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->integer('latency_ms')->default(0);
            $table->enum('status', ['success', 'error'])->default('success');
            $table->string('error_type')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_feature_routes');
        Schema::dropIfExists('ai_provider_credentials');
        Schema::dropIfExists('ai_providers');
    }
};
