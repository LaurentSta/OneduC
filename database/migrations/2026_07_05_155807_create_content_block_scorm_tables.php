<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_block_scorm_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();
            $table->string('content_block_key', 64);
            $table->string('scorm_key');
            $table->text('scorm_value');
            $table->timestamps();
        });

        Schema::create('content_block_scorm_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();
            $table->string('content_block_key', 64);
            $table->string('lesson_status')->nullable();
            $table->unsignedTinyInteger('first_score')->nullable();
            $table->unsignedTinyInteger('best_score')->nullable();
            $table->unsignedTinyInteger('last_score')->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('session_time')->default(0);
            $table->unsignedInteger('last_session_time')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lecture_id', 'content_block_key'], 'cb_scorm_scores_user_lecture_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_block_scorm_scores');
        Schema::dropIfExists('content_block_scorm_results');
    }
};
