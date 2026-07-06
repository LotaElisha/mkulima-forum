<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One vote per user per thread/reply (toggle semantics).
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('votable'); // forum_threads / forum_replies
            $table->timestamps();

            $table->unique(['user_id', 'votable_type', 'votable_id']);
        });

        // Regional sub-forums (EF-003): threads carry a region; categories can be region-scoped.
        Schema::table('forum_threads', function (Blueprint $table) {
            $table->string('region', 64)->nullable()->index()->after('language');
        });
        Schema::table('forum_categories', function (Blueprint $table) {
            $table->string('region', 64)->nullable()->index();
        });

        // Expert verification badge (EF-003).
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified_expert')->default(false);
            $table->string('expert_title')->nullable(); // e.g. "Afisa Ugani", "Agronomist"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
        Schema::table('forum_threads', fn (Blueprint $t) => $t->dropColumn('region'));
        Schema::table('forum_categories', fn (Blueprint $t) => $t->dropColumn('region'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['is_verified_expert', 'expert_title']));
    }
};
