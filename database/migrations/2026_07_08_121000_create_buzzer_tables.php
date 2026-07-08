<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buzzer_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('mode', 20)->default('presentiel');
            $table->string('access_code', 6)->unique();
            $table->string('status', 20)->default('waiting');
            $table->unsignedInteger('current_position')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'status']);
        });

        Schema::create('buzzer_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buzzer_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('question_text', 500);
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->unique(['buzzer_session_id', 'position']);
        });

        Schema::create('buzzer_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buzzer_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();

            $table->unique(['buzzer_session_id', 'user_id'], 'buzzer_participants_unique');
        });

        Schema::create('buzzer_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buzzer_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buzzer_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('reaction_ms');
            $table->string('result', 10)->nullable();
            $table->timestamps();

            $table->unique(['buzzer_question_id', 'user_id'], 'buzzer_attempts_unique_per_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzzer_attempts');
        Schema::dropIfExists('buzzer_participants');
        Schema::dropIfExists('buzzer_questions');
        Schema::dropIfExists('buzzer_sessions');
    }
};
