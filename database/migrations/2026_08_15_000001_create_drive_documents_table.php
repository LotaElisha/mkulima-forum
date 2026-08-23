<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drive_documents', function (Blueprint $table) {
            $table->id();
            $table->string('google_file_id')->unique();
            $table->string('name');
            $table->string('mime_type')->index();
            $table->unsignedBigInteger('size')->nullable();
            $table->text('web_view_link')->nullable();
            $table->text('web_content_link')->nullable();
            $table->text('icon_link')->nullable();
            $table->text('thumbnail_link')->nullable();
            $table->timestamp('drive_modified_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_documents');
    }
};
