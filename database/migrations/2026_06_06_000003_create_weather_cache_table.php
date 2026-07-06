<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_cache', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lon', 10, 6)->nullable();
            $table->json('current_data');
            $table->json('forecast_data');
            $table->json('advisory_data')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('location');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_cache');
    }
};
