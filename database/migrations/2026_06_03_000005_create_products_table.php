<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('TZS');
            $table->integer('stock_quantity')->default(0);
            $table->string('unit', 20)->default('unit'); // kg, litre, bag, etc
            $table->json('attributes')->nullable(); // brand, expiry, certification
            $table->enum('status', ['draft', 'active', 'out_of_stock', 'suspended'])->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
