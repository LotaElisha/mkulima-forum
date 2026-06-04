<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'rating')) {
                $table->decimal('rating', 2, 1)->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->default('piece')->after('stock_quantity');
            }
            if (!Schema::hasColumn('products', 'min_stock_level')) {
                $table->integer('min_stock_level')->default(10)->after('unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumnIfExists('rating');
            $table->dropColumnIfExists('unit');
            $table->dropColumnIfExists('min_stock_level');
        });
    }
};
