<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the fake cache/hardcoded responses in DroneController and
     * YieldController with real persistence (audit 2026-07-12).
     */
    public function up(): void
    {
        Schema::create('drone_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('service_id', 32); // spraying|mapping|monitoring|seeding
            $table->string('farm_location');
            $table->decimal('farm_size_acres', 8, 2);
            $table->date('preferred_date');
            $table->string('contact_phone', 20);
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 12, 2);
            $table->string('status', 20)->default('pending'); // pending|confirmed|completed|cancelled
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('yield_estimates', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('crop_type', 64);
            $table->decimal('farm_size_acres', 8, 2);
            $table->decimal('yield_per_acre', 10, 2);
            $table->decimal('estimated_yield_total', 12, 2);
            $table->string('yield_unit', 20);
            $table->decimal('price_per_unit', 12, 2);
            $table->decimal('estimated_revenue', 14, 2);
            $table->string('method', 40)->default('reference_table');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yield_estimates');
        Schema::dropIfExists('drone_bookings');
    }
};
