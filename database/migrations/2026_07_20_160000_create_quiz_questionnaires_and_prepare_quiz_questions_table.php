<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questionnaires', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
        });

        // doctrine/dbal n'est pas installé : on modifie la colonne en SQL brut
        // plutôt que via Schema::table(...)->change().
        DB::statement('ALTER TABLE quiz_questions MODIFY lecture_id BIGINT UNSIGNED NULL');

        Schema::table('quiz_questions', function (Blueprint $table): void {
            $table->foreignId('questionnaire_id')->nullable()->after('lecture_id')
                ->constrained('quiz_questionnaires')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('questionnaire_id');
        });

        DB::statement('ALTER TABLE quiz_questions MODIFY lecture_id BIGINT UNSIGNED NOT NULL');

        Schema::dropIfExists('quiz_questionnaires');
    }
};
