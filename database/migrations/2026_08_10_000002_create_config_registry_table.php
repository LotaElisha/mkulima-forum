<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string|integer|boolean|json|secret
            $table->string('group', 32)->default('general'); // verify|community|security|bus|risk|regulator
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->integer('version')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_settings');
    }
};
