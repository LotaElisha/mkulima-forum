<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic services engine (REDESIGN.md §3): one provider/booking model
     * shared by agronomist (EF-004), veterinary (EF-007) and soil testing
     * (EF-008). Logistics (EF-005) and warehouse (EF-006) are separate
     * modules — see Phase 3.
     */
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->enum('service_type', ['agronomist', 'veterinary', 'soil_testing']);
            $table->string('business_name')->nullable();
            $table->text('bio')->nullable();
            $table->json('specializations')->nullable(); // e.g. ["mahindi","kahawa"] or ["ng'ombe","kuku"]
            $table->string('region', 64)->index();
            $table->string('district', 64)->nullable();
            $table->string('license_number')->nullable(); // TVLA / TARI / lab registration
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->decimal('consultation_fee', 12, 2)->nullable(); // TZS
            $table->decimal('visit_fee', 12, 2)->nullable(); // TZS, on-farm visit
            $table->json('availability')->nullable(); // weekly schedule: {"mon":["09:00-12:00"],...}
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'service_type']);
            $table->index(['tenant_id', 'service_type', 'region']);
        });

        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('service_provider_id')->constrained('service_providers')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->enum('booking_type', ['consultation', 'farm_visit', 'sample_collection']);
            $table->text('description')->nullable(); // farmer's problem statement
            $table->json('media')->nullable(); // photos of crop/animal/soil
            $table->dateTime('scheduled_at');
            $table->string('location')->nullable(); // farm location / GPS
            $table->decimal('fee', 12, 2)->nullable(); // TZS, agreed fee
            $table->enum('status', [
                'pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show',
            ])->default('pending');
            $table->text('provider_notes')->nullable(); // diagnosis / lab results summary
            $table->json('results')->nullable(); // structured results (e.g. soil nutrient breakdown)
            $table->tinyInteger('farmer_rating')->nullable(); // 1-5
            $table->text('farmer_review')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['service_provider_id', 'scheduled_at']);
            $table->index(['farmer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
        Schema::dropIfExists('service_providers');
    }
};
