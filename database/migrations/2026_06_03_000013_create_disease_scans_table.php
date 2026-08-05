<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('image_path');
            $table->string('disease_name')->nullable();
            $table->float('confidence_score')->nullable();
            $table->text('description')->nullable();
            $table->text('treatment_recommendation')->nullable();
            $table->json('affected_areas')->nullable();
            $table->enum('scan_source', ['tflite_ondevice', 'gemini_cloud', 'manual'])->default('tflite_ondevice');
            $table->enum('status', ['pending', 'completed', 'failed', 'escalated'])->default('pending');
            $table->json('gemini_response')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_scans');
    }
};
