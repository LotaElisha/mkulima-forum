<?php

use App\Support\KycBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_verified_at')->nullable();
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_kyc_status_check');
            DB::statement(<<<'SQL'
                ALTER TABLE users ADD CONSTRAINT users_kyc_status_check CHECK (
                    kyc_status::text = ANY (ARRAY[
                        'pending'::character varying,
                        'verified'::character varying,
                        'rejected'::character varying,
                        'not_submitted'::character varying,
                        'unverified'::character varying
                    ]::text[])
                )
            SQL);
            DB::statement("ALTER TABLE users ALTER COLUMN kyc_status SET DEFAULT 'not_submitted'");
        }

        KycBackfill::notSubmittedWithoutDocuments();

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('subject')->nullable();
            $table->string('organization')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        Schema::table('forum_categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
        });

        DB::table('forum_categories')->whereNull('uuid')->orderBy('id')->pluck('id')->each(function ($id) {
            DB::table('forum_categories')->where('id', $id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        });

        DB::statement('ALTER TABLE forum_categories ALTER COLUMN uuid SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE forum_categories DROP COLUMN IF EXISTS uuid');

        Schema::dropIfExists('contact_messages');

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_kyc_status_check');
            DB::statement(<<<'SQL'
                ALTER TABLE users ADD CONSTRAINT users_kyc_status_check CHECK (
                    kyc_status::text = ANY (ARRAY[
                        'pending'::character varying,
                        'verified'::character varying,
                        'rejected'::character varying,
                        'not_submitted'::character varying
                    ]::text[])
                )
            SQL);
            DB::statement("ALTER TABLE users ALTER COLUMN kyc_status SET DEFAULT 'pending'");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_submitted_at',
                'kyc_verified_at',
                'app_authentication_secret',
                'app_authentication_recovery_codes',
            ]);
        });
    }
};
