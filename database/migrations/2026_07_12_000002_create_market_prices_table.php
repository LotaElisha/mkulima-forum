<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('commodity', 64);          // e.g. mahindi, mpunga
            $table->string('market', 96);             // e.g. Kariakoo, Soko Kuu Arusha
            $table->string('region', 64);             // e.g. Dar es Salaam, Arusha
            $table->decimal('min_price', 12, 2);
            $table->decimal('max_price', 12, 2);
            $table->decimal('avg_price', 12, 2);
            $table->string('unit', 32);               // e.g. kg, gunia la kg 100, lita
            $table->string('currency', 8)->default('TZS');
            $table->date('price_date');               // date the price was recorded
            $table->string('source', 128)->nullable();// e.g. Wizara ya Kilimo, soko lenyewe
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['commodity', 'region', 'price_date']);
            $table->index(['market', 'price_date']);
            $table->index('price_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};
