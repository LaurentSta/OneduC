<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->json('questions');
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('poll_session_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_session_id')->constrained('poll_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('question_index');
            $table->unsignedSmallInteger('choice_index');
            $table->timestamps();

            $table->unique(
                ['poll_session_id', 'user_id', 'question_index'],
                'poll_session_responses_unique_answer'
            );
            $table->index(['poll_session_id', 'question_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_session_responses');
        Schema::dropIfExists('poll_sessions');
    }
};
