<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Becoming a seller is a decision with a paper trail, not a checkbox.
 *
 * Before this table there was no such thing as "applying to sell": the app
 * showed every farmer a Seller Dashboard entry, the API refused it with 403,
 * and the farmer was left holding an error message that explained nothing.
 * The middle states - applied, waiting, refused and why - had nowhere to live.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('business_name');
            $table->string('business_type');       // agrodealer | farmer_producer | cooperative | transporter
            $table->string('region');
            $table->string('district')->nullable();
            $table->string('contact_phone');
            $table->text('description')->nullable();

            // pending | approved | rejected
            $table->string('status')->default('pending')->index();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            // One live application per user. A farmer refused once may apply
            // again, so this is not unique on user_id alone.
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_applications');
    }
};
