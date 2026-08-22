<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production-ready email authentication support.
 *
 * Before this migration the platform had no password reset path at all and no
 * way to change an email address safely: `PUT /api/auth/profile` swapped the
 * address in place with no proof of ownership and no re-verification.
 *
 * - password_reset_tokens: the standard Laravel broker table (it was never
 *   published for this project, so Password::broker() had nothing to write to).
 * - users.pending_email: an email change is staged here and only promoted to
 *   users.email once the new address proves ownership, so a hijacked session
 *   cannot silently move the account to an attacker-controlled inbox.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pending_email')) {
                $table->string('pending_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'pending_email_requested_at')) {
                $table->timestamp('pending_email_requested_at')->nullable()->after('pending_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pending_email', 'pending_email_requested_at']);
        });

        Schema::dropIfExists('password_reset_tokens');
    }
};
