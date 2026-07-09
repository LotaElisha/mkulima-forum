<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transporters', function (Blueprint $table) {
            $table->unsignedInteger('rating_count')->default(0)->after('rating');
        });

        Schema::table('freight_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('requester_rating')->nullable()->after('status');
            $table->text('requester_review')->nullable()->after('requester_rating');
        });
    }

    public function down(): void
    {
        Schema::table('transporters', function (Blueprint $table) {
            $table->dropColumn('rating_count');
        });

        Schema::table('freight_requests', function (Blueprint $table) {
            $table->dropColumn(['requester_rating', 'requester_review']);
        });
    }
};
