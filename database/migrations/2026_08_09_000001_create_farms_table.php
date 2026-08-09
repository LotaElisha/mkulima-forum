<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('location');
            $table->decimal('size_acres', 8, 2)->default(1.0);
            $table->string('crop_type');
            $table->string('soil_type')->nullable();
            $table->date('planting_date')->nullable();
            $table->date('harvest_expected_date')->nullable();
            $table->string('status')->default('active'); // active, harvesting, fallow, archived
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
