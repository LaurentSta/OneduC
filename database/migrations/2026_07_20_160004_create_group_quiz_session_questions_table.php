<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_quiz_session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['group_quiz_session_id', 'position'], 'group_quiz_questions_unique_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_quiz_session_questions');
    }
};
