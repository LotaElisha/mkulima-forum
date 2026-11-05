<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mkulima Trace: follow a crop lot from the plot to the buyer.
     *
     * Lots and events carry UUIDs made on the phone, so a sync that is retried
     * after a dropped connection finds the row it already wrote instead of
     * writing a second one. QR codes carry only `code`, a random token; never
     * a name, a phone number or coordinates.
     */
    public function up(): void
    {
        // Who may take part, and as what. Separate from users.role so a
        // cooperative or processor does not need a new privileged role.
        Schema::create('trace_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('code', 16)->unique();
            // farmer | collector | warehouse | processor | transporter | buyer
            $table->string('type', 16)->index();
            $table->string('organisation_name', 120)->nullable();
            $table->string('region', 64)->nullable();
            // active: may record. pending: waiting for an admin. rejected, suspended.
            $table->string('status', 16)->default('pending')->index();
            // Set only when an admin has checked the participant.
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_note', 500)->nullable();
            // The farmer's choice to show their phone to people in their lots' chain.
            $table->boolean('share_contact_with_chain')->default(false);
            $table->timestamps();
        });

        Schema::create('trace_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('trace_participants')->onDelete('cascade');
            $table->foreignId('farm_id')->nullable()->constrained('farms')->nullOnDelete();
            $table->string('uuid', 36)->unique();
            $table->string('code', 16)->unique();
            $table->string('name', 120);
            $table->decimal('area_acres', 10, 2)->nullable();
            $table->string('region', 64);
            $table->string('district', 64)->nullable();
            $table->string('village', 64)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->boolean('share_location_with_chain')->default(false);
            $table->timestamps();
        });

        Schema::create('trace_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('plot_id')->constrained('trace_plots')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('crop', 64);
            $table->string('season_label', 64);
            $table->date('planted_on')->nullable();
            $table->date('expected_harvest_on')->nullable();
            $table->string('status', 16)->default('open');
            $table->timestamps();
        });

        Schema::create('trace_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('code', 16)->unique();
            $table->string('crop', 64);
            $table->string('product', 120)->nullable(); // after processing, e.g. "unga wa mahindi"
            $table->foreignId('season_id')->nullable()->constrained('trace_seasons')->nullOnDelete();
            $table->foreignId('plot_id')->nullable()->constrained('trace_plots')->nullOnDelete();
            $table->foreignId('origin_participant_id')->nullable()->constrained('trace_participants')->nullOnDelete();
            $table->foreignId('holder_participant_id')->constrained('trace_participants');
            // Set while a handoff waits for the second party to confirm.
            $table->foreignId('pending_holder_participant_id')->nullable()->constrained('trace_participants')->nullOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->string('unit', 16);
            $table->decimal('quantity_kg', 14, 3);
            $table->string('grade', 32)->nullable();
            // harvest | split | merge | processing
            $table->string('origin', 16);
            // active | in_transfer | split | merged | processed | delivered | closed
            $table->string('status', 16)->default('active')->index();
            $table->date('harvested_on')->nullable();
            $table->json('anomaly_flags')->nullable();
            $table->timestamp('anomaly_reviewed_at')->nullable();
            $table->foreignId('anomaly_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'holder_participant_id', 'status']);
        });

        Schema::create('trace_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->foreignId('lot_id')->constrained('trace_lots')->onDelete('cascade');
            $table->string('type', 16)->index();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('recorder_participant_id')->constrained('trace_participants');
            $table->foreignId('from_participant_id')->nullable()->constrained('trace_participants')->nullOnDelete();
            $table->foreignId('to_participant_id')->nullable()->constrained('trace_participants')->nullOnDelete();
            $table->foreignId('transporter_participant_id')->nullable()->constrained('trace_participants')->nullOnDelete();
            $table->decimal('quantity', 14, 3)->nullable();
            $table->string('unit', 16)->nullable();
            $table->decimal('quantity_kg', 14, 3)->nullable();
            $table->decimal('received_kg', 14, 3)->nullable();
            $table->decimal('loss_kg', 14, 3)->nullable();
            $table->string('loss_reason', 255)->nullable();
            $table->decimal('discrepancy_kg', 14, 3)->nullable();
            $table->string('grade', 32)->nullable();
            $table->decimal('moisture_percent', 5, 2)->nullable();
            $table->string('quality_notes', 500)->nullable();
            $table->decimal('price_per_kg', 14, 2)->nullable();
            $table->string('currency', 3)->nullable();
            // Typed in by a participant. Mkulima Trace does not move money and
            // does not check these against any payment provider.
            $table->string('payment_method', 16)->nullable();
            $table->string('payment_reference', 64)->nullable();
            $table->string('location_label', 120)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('freight_request_id')->nullable()->constrained('freight_requests')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('evidence_path')->nullable();
            // not_required | pending | confirmed | disputed
            $table->string('confirmation_status', 16)->default('not_required')->index();
            $table->timestamp('sender_confirmed_at')->nullable();
            $table->timestamp('receiver_confirmed_at')->nullable();
            $table->string('dispute_reason', 500)->nullable();
            // When it happened on the ground (phone clock), and when it reached the server.
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['lot_id', 'occurred_at']);
        });

        // Parent and child lots. Splits, merges and processing add rows here;
        // the parents and their events are never rewritten.
        Schema::create('trace_lot_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_lot_id')->constrained('trace_lots')->onDelete('cascade');
            $table->foreignId('child_lot_id')->constrained('trace_lots')->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained('trace_events')->nullOnDelete();
            $table->string('relation', 16); // split | merge | processing
            $table->decimal('quantity_kg', 14, 3);
            $table->timestamps();

            $table->unique(['parent_lot_id', 'child_lot_id']);
        });

        // A Plant AI scan the farmer chose to attach to a season.
        Schema::create('trace_season_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('trace_seasons')->onDelete('cascade');
            $table->foreignId('disease_scan_id')->constrained('disease_scans')->onDelete('cascade');
            $table->foreignId('linked_by')->constrained('users');
            $table->timestamp('consented_at');
            $table->timestamps();

            $table->unique(['season_id', 'disease_scan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trace_season_observations');
        Schema::dropIfExists('trace_lot_links');
        Schema::dropIfExists('trace_events');
        Schema::dropIfExists('trace_lots');
        Schema::dropIfExists('trace_seasons');
        Schema::dropIfExists('trace_plots');
        Schema::dropIfExists('trace_participants');
    }
};
