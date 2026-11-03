<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Equipment rentals, and coordinates on service providers so the app can
     * list agronomists by distance. Coordinates are optional everywhere: a
     * farmer who declines location still gets results by region.
     */
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->decimal('latitude', 9, 6)->nullable()->after('district');
            $table->decimal('longitude', 9, 6)->nullable()->after('latitude');
        });

        Schema::create('equipment_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('category', 32)->index();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->decimal('daily_rate', 12, 2); // TZS per day
            $table->boolean('with_operator')->default(false);
            $table->string('region', 64)->index();
            $table->string('district', 64)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            // active: listed. paused: owner hid it. hidden: an admin hid it.
            $table->string('status', 16)->default('active')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'category']);
        });

        Schema::create('equipment_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('equipment_listing_id')->constrained('equipment_listings')->onDelete('cascade');
            $table->foreignId('renter_id')->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('quoted_total', 12, 2); // daily_rate x days at request time
            $table->text('message')->nullable();
            // requested -> accepted | declined | cancelled; accepted -> completed | cancelled
            $table->string('status', 16)->default('requested')->index();
            $table->timestamps();

            $table->index(['equipment_listing_id', 'status', 'start_date']);
            $table->index(['renter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_rentals');
        Schema::dropIfExists('equipment_listings');
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
