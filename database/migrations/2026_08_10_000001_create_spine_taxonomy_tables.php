<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_units', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('type', 20); // country|region|district|ward|village
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('boundary_geojson')->nullable();
            $table->timestamps();

            $table->index(['type', 'parent_id']);
            $table->index('name');
        });

        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name'); // e.g. Maize / Mahindi
            $table->string('slug', 64)->unique();
            $table->string('swahili_name')->nullable();
            $table->string('category', 32)->default('cereal'); // cereal|legume|cash_crop|vegetable|fruit|tuber
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('agricultural_topics', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug', 64)->unique();
            $table->string('swahili_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name'); // e.g. Seed, Fertilizer, Pesticide, Vet, Equipment
            $table->string('slug', 64)->unique();
            $table->string('swahili_name')->nullable();
            $table->string('code', 20)->unique(); // SEED, FERTILIZER, PESTICIDE, VET, EQUIP, OTHER
            $table->boolean('requires_certification')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('agricultural_topics');
        Schema::dropIfExists('crops');
        Schema::dropIfExists('geo_units');
    }
};
