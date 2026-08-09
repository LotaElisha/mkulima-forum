<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('activity_type'); // Kupanda, Kupalilia, Kupiga Dawa, Kuweka Mbolea, Kuvuna, Uwagiliaji
            $table->date('activity_date');
            $table->decimal('cost_tzs', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};
