<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_prices', function (Blueprint $table) {
            $table->string('price_type', 32)->nullable()->after('region');
            $table->decimal('fx_rate', 12, 4)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('market_prices', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'fx_rate']);
        });
    }
};
