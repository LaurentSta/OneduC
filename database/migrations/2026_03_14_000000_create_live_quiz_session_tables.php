<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_quiz_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('module_sections')->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();
            $table->string('access_code', 12)->unique();
            $table->string('status', 32)->default('waiting');
            $table->unsignedInteger('current_position')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamp('answer_revealed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['lecture_id', 'status']);
            $table->index(['formateur_id', 'ended_at']);
        });

        Schema::create('live_quiz_session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['live_quiz_session_id', 'position'], 'live_quiz_session_questions_session_position_unique');
            $table->unique(['live_quiz_session_id', 'question_id'], 'live_quiz_session_questions_session_question_unique');
        });

        Schema::create('live_quiz_session_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_quiz_session_id')->constrained('live_quiz_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['live_quiz_session_id', 'user_id'], 'live_quiz_session_participants_session_user_unique');
            $table->unique(['live_quiz_session_id', 'attempt_id'], 'live_quiz_session_participants_session_attempt_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_quiz_session_participants');
        Schema::dropIfExists('live_quiz_session_questions');
        Schema::dropIfExists('live_quiz_sessions');
    }
};
