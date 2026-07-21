<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};
