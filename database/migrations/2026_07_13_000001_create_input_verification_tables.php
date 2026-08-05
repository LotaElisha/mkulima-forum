<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Kagua Dawa" — counterfeit agricultural-input detection:
     *
     * - registered_inputs: the official registry (TPRI pesticides / TFRA
     *   fertilizers) loaded by admins from official sources. Empty until
     *   populated — the API says so honestly instead of guessing.
     * - counterfeit_alerts: community reports of suspected fakes, reviewed
     *   by admins; only confirmed alerts are shown publicly per region.
     */
    public function up(): void
    {
        Schema::create('registered_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('type', 32); // pesticide|herbicide|fungicide|insecticide|fertilizer|vet_product|seed
            $table->string('registration_number', 64)->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('distributor')->nullable();
            $table->string('status', 20)->default('registered'); // registered|banned|withdrawn
            $table->string('source', 128); // e.g. "TPRI Registered Pesticides List 2026"
            $table->date('source_date')->nullable(); // date of the official list
            $table->timestamps();

            $table->index('name');
            $table->index('registration_number');
            $table->index(['type', 'status']);
        });

        Schema::create('counterfeit_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_type', 32)->nullable();
            $table->string('registration_number', 64)->nullable();
            $table->string('batch_number', 64)->nullable();
            $table->string('dealer_name')->nullable();   // duka/agrovet
            $table->string('region', 64);
            $table->string('district', 64)->nullable();
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('pending'); // pending|confirmed|dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['region', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counterfeit_alerts');
        Schema::dropIfExists('registered_inputs');
    }
};
