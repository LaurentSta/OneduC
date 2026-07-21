<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_quiz_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->cascadeOnDelete();
            $table->string('access_code', 6)->unique();
            $table->string('status', 20)->default('waiting');
            $table->unsignedInteger('current_position')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamp('answer_revealed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'status']);
        });

        Schema::create('group_quiz_session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['group_quiz_session_id', 'position'], 'group_quiz_questions_unique_position');
        });

        Schema::create('group_quiz_session_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['group_quiz_session_id', 'user_id'], 'group_quiz_participants_unique_user');
        });

        Schema::create('group_quiz_session_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_quiz_session_question_id');
            $table->foreign('group_quiz_session_question_id', 'group_quiz_answers_question_fk')
                ->references('id')->on('group_quiz_session_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('answer_option_ids')->nullable();
            $table->json('given_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['group_quiz_session_question_id', 'user_id'], 'group_quiz_answers_unique_per_question');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_quiz_session_answers');
        Schema::dropIfExists('group_quiz_session_participants');
        Schema::dropIfExists('group_quiz_session_questions');
        Schema::dropIfExists('group_quiz_sessions');
    }
};
