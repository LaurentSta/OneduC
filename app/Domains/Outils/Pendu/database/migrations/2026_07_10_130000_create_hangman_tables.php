<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hangman_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('word', 60);
            $table->unsignedTinyInteger('max_attempts')->default(6);
            $table->json('guessed_letters')->nullable();
            $table->string('status', 20)->default('in_progress');
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('hangman_guesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hangman_session_id')->constrained('hangman_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('letter', 1);
            $table->boolean('correct');
            $table->timestamps();

            $table->unique(['hangman_session_id', 'letter'], 'hangman_guesses_unique_letter');
            $table->index(['hangman_session_id', 'user_id'], 'hangman_guesses_session_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hangman_guesses');
        Schema::dropIfExists('hangman_sessions');
    }
};
