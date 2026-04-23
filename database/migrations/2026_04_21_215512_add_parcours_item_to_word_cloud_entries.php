<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_cloud_entries', function (Blueprint $table) {
            $table->foreignId('formateur_parcours_item_id')
                ->nullable()
                ->after('word_cloud_id')
                ->constrained('formateur_parcours_items')
                ->nullOnDelete();

            $table->unsignedTinyInteger('question_index')
                ->default(0)
                ->after('formateur_parcours_item_id');

            $table->unique(
                ['formateur_parcours_item_id', 'question_index', 'user_id'],
                'unique_parcours_wc_answer'
            );
        });

        // Rendre word_cloud_id nullable (les entrées parcours n'ont pas de WordCloud ad-hoc)
        Schema::table('word_cloud_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('word_cloud_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('word_cloud_entries', function (Blueprint $table) {
            $table->dropUnique('unique_parcours_wc_answer');
            $table->dropForeign(['formateur_parcours_item_id']);
            $table->dropColumn(['formateur_parcours_item_id', 'question_index']);
            $table->unsignedBigInteger('word_cloud_id')->nullable(false)->change();
        });
    }
};
