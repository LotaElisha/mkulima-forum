<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('store_name')->nullable()->after('name');
            $table->text('store_location')->nullable()->after('store_name');
            $table->string('business_license')->nullable()->after('store_location');
            $table->text('store_description')->nullable()->after('business_license');
            $table->timestamp('suspended_at')->nullable()->after('updated_at');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'store_name', 'store_location', 'business_license',
                'store_description', 'suspended_at', 'suspension_reason'
            ]);
        });
    }
};