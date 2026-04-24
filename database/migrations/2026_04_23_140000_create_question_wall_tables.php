<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_walls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('question_wall_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_wall_id')->constrained('question_walls')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_anonymous')->default(false);
            $table->string('status', 16)->default('open'); // open|treated|activity|later
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->index(['question_wall_id', 'status']);
            $table->index(['question_wall_id', 'created_at']);
        });

        Schema::create('question_wall_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_wall_question_id')->constrained('question_wall_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['question_wall_question_id', 'user_id'], 'question_wall_votes_question_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_wall_votes');
        Schema::dropIfExists('question_wall_questions');
        Schema::dropIfExists('question_walls');
    }
};
