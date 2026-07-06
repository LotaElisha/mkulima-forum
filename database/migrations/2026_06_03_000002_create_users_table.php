<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('name');
            $table->string('avatar')->nullable();
            // String (not enum): role list must match App\Support\Roles::ALL and
            // the Spatie roles seeded in RolesAndPermissionsSeeder.
            $table->string('role', 32)->default('farmer');
            $table->enum('kyc_status', ['pending', 'verified', 'rejected', 'not_submitted'])->default('pending');
            $table->json('kyc_documents')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->string('passkey_id')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->string('preferred_language', 5)->default('sw');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['tenant_id', 'role']);
            $table->index(['tenant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
