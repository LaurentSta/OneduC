<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_path_activity_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module_key');
            $table->string('chapter_key');
            $table->string('lesson_key');
            $table->string('activity_key');
            $table->string('activity_type')->default('sorting');
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('correct_items')->default(0);
            $table->boolean('is_success')->default(false);
            $table->json('submitted_answer');
            $table->json('expected_answer');
            $table->json('wrong_items')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['user_id', 'module_key', 'chapter_key', 'lesson_key'], 'trainer_path_activity_user_module_idx');
            $table->index(['activity_key', 'is_success'], 'trainer_path_activity_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_path_activity_attempts');
    }
};
