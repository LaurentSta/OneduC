<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};
