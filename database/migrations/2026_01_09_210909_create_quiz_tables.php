<?php

// database/migrations/xxxx_create_quiz_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {

    Schema::create('quiz_questions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();
      $table->enum('type', ['single','multiple','boolean']); // choix unique, choix multiple, vrai/faux :contentReference[oaicite:2]{index=2}
      $table->text('question_text');

      $table->string('image_path')->nullable();
      $table->string('image_alt')->nullable();     // accessibilité :contentReference[oaicite:3]{index=3}
      $table->string('audio_path')->nullable();
      $table->text('audio_transcript')->nullable(); // accessibilité :contentReference[oaicite:4]{index=4}

      $table->boolean('is_active')->default(true);
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();

      $table->index(['lecture_id','is_active']);
    });

    Schema::create('quiz_options', function (Blueprint $table) {
      $table->id();
      $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
      $table->text('option_text');
      $table->boolean('is_correct')->default(false);
      $table->unsignedInteger('position')->default(0);
      $table->timestamps();

      $table->index(['question_id','is_correct']);
    });

    Schema::create('quiz_attempts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();

      $table->timestamp('started_at')->nullable();
      $table->timestamp('finished_at')->nullable();

      $table->unsignedInteger('total_questions')->default(0);
      $table->unsignedInteger('score')->default(0);   // points
      $table->unsignedInteger('percent')->default(0); // %
      $table->boolean('passed')->default(false);      // 100% requis :contentReference[oaicite:5]{index=5}

      $table->unsignedInteger('total_time_seconds')->default(0); // traçabilité :contentReference[oaicite:6]{index=6}
      $table->timestamps();

      $table->index(['user_id','lecture_id','passed']);
    });

    Schema::create('quiz_attempt_questions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
      $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
      $table->unsignedInteger('position')->default(0);

      $table->timestamp('question_started_at')->nullable();
      $table->timestamp('answered_at')->nullable();
      $table->unsignedInteger('time_seconds')->default(0); // temps par question :contentReference[oaicite:7]{index=7}

      $table->json('answer_option_ids')->nullable(); // réponses choisies
      $table->boolean('is_correct')->default(false);

      $table->timestamps();

      $table->unique(['attempt_id','question_id']);
      $table->index(['attempt_id','position']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('quiz_attempt_questions');
    Schema::dropIfExists('quiz_attempts');
    Schema::dropIfExists('quiz_options');
    Schema::dropIfExists('quiz_questions');
  }
};
