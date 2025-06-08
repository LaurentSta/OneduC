<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scorm_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();

            $table->integer('first_score')->nullable();
            $table->integer('last_score')->nullable();
            $table->integer('best_score')->nullable();
            $table->integer('attempts_count')->default(0);
            $table->integer('questions_answered')->default(0);
            $table->integer('session_time')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->string('lesson_status')->nullable();
            $table->boolean('is_completed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_evaluation_scores');
    }
};
