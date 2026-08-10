<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Regulatory Authorities (TOSCI, TPHPA, TBS, TFRA, MAFC, CUSTOM)
        Schema::create('regulatory_authorities', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name'); // e.g. Tanzania Plant Health and Pesticides Authority
            $table->string('acronym', 20)->unique(); // TPHPA, TOSCI, TBS, TFRA, MAFC, CUSTOM
            $table->string('country', 2)->default('TZ');
            $table->json('product_categories')->nullable(); // ["SEED","PESTICIDE",...]
            $table->boolean('is_active')->default(true);
            $table->text('display_note')->nullable(); // e.g. "Independent regulatory authority data"
            $table->timestamps();
        });

        // Regulatory Data Sources (Configurable backing modes per regulator)
        Schema::create('regulatory_data_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authority_id')->constrained('regulatory_authorities')->cascadeOnDelete();
            $table->string('name');
            $table->string('source_url')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->text('api_key_encrypted')->nullable(); // Encrypted server-side
            $table->string('auth_type', 32)->default('none'); // none|api_key|bearer|oauth2|basic
            $table->string('backing_mode', 32)->default('manual_import'); // manual_import|admin_entered|public_dataset|periodic_sync|official_api
            $table->integer('sync_interval_minutes')->default(1440);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('data_version', 32)->default('1.0');
            $table->integer('confidence_level')->default(90); // 0-100
            $table->timestamps();
        });

        // Regulatory Sync Logs
        Schema::create('regulatory_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('regulatory_data_sources')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('rows_imported')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->json('diff_summary')->nullable();
            $table->timestamps();
        });

        // Manufacturers
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('country', 2)->default('TZ');
            $table->string('registration_number', 64)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('provenance', 20)->default('PLATFORM'); // REGULATORY|PLATFORM|AI|COMMUNITY
            $table->timestamps();

            $table->index('name');
        });

        // Regulated Products (Seeds, Fertilizers, Pesticides, Vet products)
        Schema::create('regulated_products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('authority_id')->nullable()->constrained('regulatory_authorities')->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->string('registration_number', 64);
            $table->string('trade_name');
            $table->string('active_ingredient')->nullable(); // for pesticides
            $table->string('formulation')->nullable();
            $table->json('permitted_crops')->nullable();
            $table->json('target_pests')->nullable();
            $table->string('registration_status', 32)->default('REGISTERED'); // REGISTERED|BANNED|WITHDRAWN|SUSPENDED|EXPIRED
            $table->date('expiry_date')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('provenance', 20)->default('REGULATORY'); // REGULATORY|PLATFORM|AI|COMMUNITY
            $table->integer('confidence')->default(100);
            $table->timestamp('as_of')->useCurrent();
            $table->timestamps();

            $table->unique(['registration_number', 'authority_id']);
            $table->index('trade_name');
            $table->index('registration_status');
        });

        // Product Batches
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('regulated_products')->cascadeOnDelete();
            $table->string('batch_number', 64)->index();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE|RECALLED|EXPIRED|SUSPENDED
            $table->timestamps();
        });

        // Product Serials (Serialization and Track & Trace)
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('regulated_products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->string('gtin', 14)->nullable();
            $table->string('internal_serial', 64)->unique();
            $table->string('manufacturer_serial', 64)->nullable();
            $table->string('current_holder_type', 32)->nullable(); // manufacturer|distributor|agrodealer|farmer
            $table->unsignedBigInteger('current_holder_id')->nullable();
            $table->boolean('is_duplicate_detected')->default(false);
            $table->timestamps();

            $table->index('manufacturer_serial');
        });

        // Agrodealers (KYC & Trust Level)
        Schema::create('agrodealers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('business_name');
            $table->string('owner_name')->nullable();
            $table->string('business_registration', 64)->nullable();
            $table->string('tin', 32)->nullable();
            $table->string('business_licence', 64)->nullable();
            $table->text('physical_address')->nullable();
            $table->foreignId('geo_unit_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->string('regulator_licence_number', 64)->nullable();
            $table->foreignId('authority_id')->nullable()->constrained('regulatory_authorities')->nullOnDelete();
            $table->date('licence_expiry')->nullable();
            $table->string('status', 32)->default('PENDING'); // PENDING|DOCUMENTS_SUBMITTED|MKULIMA_VERIFIED|REGULATOR_RECORD_MATCHED|VERIFICATION_FAILED|SUSPENDED|EXPIRED
            $table->json('kyc_documents')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('regulator_licence_number');
        });

        // Verification Scans
        Schema::create('verification_scans', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('scanner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scan_method', 20)->default('barcode'); // barcode|qr|serial|registration|scratch|manual
            $table->text('raw_input');
            $table->foreignId('product_id')->nullable()->constrained('regulated_products')->nullOnDelete();
            $table->foreignId('geo_unit_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->boolean('is_offline')->default(false);
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['occurred_at', 'is_offline']);
        });

        // Verification Results
        Schema::create('verification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('verification_scans')->cascadeOnDelete();
            $table->string('status', 32); // VERIFIED|REGISTERED_SOURCE_CONFIRMED|COMMUNITY_SUPPLIER_RECORD|UNVERIFIED|SUSPICIOUS|RECALLED|SUSPENDED|EXPIRED
            $table->string('provenance', 20)->default('REGULATORY'); // REGULATORY|PLATFORM|AI|COMMUNITY
            $table->integer('confidence')->default(100);
            $table->json('reasons'); // ["Registration match found", "Seller licence verified"]
            $table->json('recommended_action'); // {sw: "...", en: "..."}
            $table->integer('risk_score')->default(0); // 0-100
            $table->timestamp('as_of')->useCurrent();
            $table->timestamps();

            $table->index('status');
        });

        // Risk Signals
        Schema::create('risk_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('regulated_products')->nullOnDelete();
            $table->foreignId('scan_id')->nullable()->constrained('verification_scans')->nullOnDelete();
            $table->string('signal_type', 32); // registry_miss|batch_miss|seller_unverified|duplicate_serial|geo_impossible|scan_frequency|complaint_frequency|expiry|recall|packaging_ai|chain_break
            $table->decimal('value', 8, 2)->default(0);
            $table->decimal('weight', 8, 2)->default(1);
            $table->string('provenance', 20)->default('PLATFORM');
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
        });

        // Counterfeit Reports (Incidents)
        Schema::create('counterfeit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('case_number', 32)->unique(); // MF-CF-2026-000001
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('regulated_products')->nullOnDelete();
            $table->string('product_name');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('serial_number', 64)->nullable();
            $table->string('batch_number', 64)->nullable();
            $table->foreignId('dealer_id')->nullable()->constrained('agrodealers')->nullOnDelete();
            $table->string('dealer_name_raw')->nullable();
            $table->date('purchase_date')->nullable();
            $table->foreignId('geo_unit_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->text('description');
            $table->foreignId('crop_affected_id')->nullable()->constrained('crops')->nullOnDelete();
            $table->string('status', 20)->default('RECEIVED'); // RECEIVED|UNDER_REVIEW|ESCALATED|RESOLVED|DISMISSED
            $table->string('contact_preference', 20)->default('none');
            $table->boolean('reporter_anonymous')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('case_number');
        });

        // Counterfeit Evidence
        Schema::create('counterfeit_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('counterfeit_reports')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type', 32)->default('image/jpeg');
            $table->string('sha256_hash', 64); // Hashed evidence for regulatory integrity
            $table->string('evidence_type', 32)->default('photo_front'); // photo_front|photo_back|receipt|barcode|seal|other
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });

        // Regulatory Cases (Escalation)
        Schema::create('regulatory_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('counterfeit_reports')->cascadeOnDelete();
            $table->string('case_number', 32);
            $table->string('escalation_mode', 32)->default('INTERNAL_ONLY'); // INTERNAL_ONLY|EMAIL_REGULATOR|API_SUBMISSION|WEBHOOK|DISTRICT_OFFICER|MANUAL_EXPORT
            $table->foreignId('authority_id')->nullable()->constrained('regulatory_authorities')->nullOnDelete();
            $table->string('status', 32)->default('DRAFT');
            $table->string('case_file_pdf_path')->nullable();
            $table->string('case_file_json_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // Recall Notices
        Schema::create('recall_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('regulated_products')->cascadeOnDelete();
            $table->foreignId('authority_id')->nullable()->constrained('regulatory_authorities')->nullOnDelete();
            $table->string('recall_type', 32)->default('VOLUNTARY'); // VOLUNTARY|MANDATORY|SAFETY_ALERT
            $table->text('reason');
            $table->json('affected_batches')->nullable();
            $table->json('geo_scope')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->string('provenance', 20)->default('REGULATORY');
            $table->timestamps();
        });

        // Farmer Awareness Advisories
        Schema::create('advisories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('type', 32); // counterfeit_alert|recall|licence_warning|unsafe_pesticide|seasonal_seed|education
            $table->json('title'); // {sw: "...", en: "..."}
            $table->json('body');  // {sw: "...", en: "..."}
            $table->json('geo_unit_ids')->nullable();
            $table->json('crop_ids')->nullable();
            $table->json('topic_ids')->nullable();
            $table->string('farmer_type_filter', 32)->nullable();
            $table->json('channel_targets')->nullable(); // ["push", "sms", "whatsapp", "in_app"]
            $table->string('status', 20)->default('DRAFT'); // DRAFT|SCHEDULED|SENT|ARCHIVED
            $table->foreignId('composed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // Advisory Deliveries
        Schema::create('advisory_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisory_id')->constrained('advisories')->cascadeOnDelete();
            $table->string('channel_driver', 32);
            $table->integer('recipient_count')->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisory_deliveries');
        Schema::dropIfExists('advisories');
        Schema::dropIfExists('recall_notices');
        Schema::dropIfExists('regulatory_cases');
        Schema::dropIfExists('counterfeit_evidence');
        Schema::dropIfExists('counterfeit_reports');
        Schema::dropIfExists('risk_signals');
        Schema::dropIfExists('verification_results');
        Schema::dropIfExists('verification_scans');
        Schema::dropIfExists('agrodealers');
        Schema::dropIfExists('product_serials');
        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('regulated_products');
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('regulatory_sync_logs');
        Schema::dropIfExists('regulatory_data_sources');
        Schema::dropIfExists('regulatory_authorities');
    }
};
