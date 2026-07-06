<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 3 scaffolding (EF-005 Logistics, EF-006 Warehouse).
     * Schema-first: tables exist so mobile/web clients can be built against
     * a stable contract; controllers/business logic land in Phase 3 proper.
     */
    public function up(): void
    {
        // EF-005: transporters (bodaboda → truck) and freight requests
        Schema::create('transporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->enum('vehicle_type', ['bodaboda', 'bajaji', 'pickup', 'canter', 'lorry', 'refrigerated']);
            $table->string('plate_number', 20)->nullable();
            $table->decimal('capacity_kg', 10, 2)->nullable();
            $table->string('base_region', 64)->index();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('freight_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transporter_id')->nullable()->constrained('transporters')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('uuid', 36)->unique();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->json('pickup_coords')->nullable();   // {lat, lng}
            $table->json('dropoff_coords')->nullable();
            $table->decimal('cargo_weight_kg', 10, 2)->nullable();
            $table->text('cargo_description')->nullable();
            $table->decimal('quoted_fare', 12, 2)->nullable(); // TZS
            $table->dateTime('pickup_at')->nullable();
            $table->enum('status', [
                'open', 'quoted', 'accepted', 'in_transit', 'delivered', 'cancelled',
            ])->default('open');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        // EF-006: storage facilities and bookings
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->enum('storage_type', ['dry', 'cold', 'grain_silo', 'general']);
            $table->string('region', 64)->index();
            $table->string('location')->nullable();
            $table->decimal('capacity_tons', 10, 2);
            $table->decimal('available_tons', 10, 2);
            $table->decimal('price_per_ton_month', 12, 2); // TZS
            $table->json('features')->nullable(); // ["fumigation","weighbridge","24h_security"]
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('produce_type', 64); // mahindi, mpunga, korosho...
            $table->decimal('quantity_tons', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_cost', 12, 2)->nullable(); // TZS
            $table->enum('status', [
                'pending', 'confirmed', 'stored', 'withdrawn', 'cancelled',
            ])->default('pending');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_bookings');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('freight_requests');
        Schema::dropIfExists('transporters');
    }
};
