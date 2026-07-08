<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('true_false_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title');
            $table->json('questions');
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('true_false_session_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('true_false_session_id')->constrained('true_false_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('question_index');
            $table->boolean('answer');
            $table->timestamps();

            $table->unique(
                ['true_false_session_id', 'user_id', 'question_index'],
                'true_false_session_responses_unique_answer'
            );
            $table->index(['true_false_session_id', 'question_index'], 'true_false_session_responses_session_question_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('true_false_session_responses');
        Schema::dropIfExists('true_false_sessions');
    }
};
