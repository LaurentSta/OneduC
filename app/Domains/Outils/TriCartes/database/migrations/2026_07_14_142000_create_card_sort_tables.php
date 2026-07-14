<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_sort_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('card_sort_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_sort_session_id')->constrained('card_sort_sessions')->cascadeOnDelete();
            $table->string('label', 120);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['card_sort_session_id', 'position']);
        });

        Schema::create('card_sort_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_sort_session_id')->constrained('card_sort_sessions')->cascadeOnDelete();
            $table->foreignId('correct_category_id')->constrained('card_sort_categories')->cascadeOnDelete();
            $table->text('text')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['card_sort_session_id', 'position']);
        });

        Schema::create('card_sort_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_sort_session_id')->constrained('card_sort_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['card_sort_session_id', 'user_id'], 'card_sort_attempts_unique_per_user');
        });

        Schema::create('card_sort_placements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_sort_attempt_id')->constrained('card_sort_attempts')->cascadeOnDelete();
            $table->foreignId('card_sort_card_id')->constrained('card_sort_cards')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('card_sort_categories')->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['card_sort_attempt_id', 'card_sort_card_id'], 'card_sort_placements_unique_per_card');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_sort_placements');
        Schema::dropIfExists('card_sort_attempts');
        Schema::dropIfExists('card_sort_cards');
        Schema::dropIfExists('card_sort_categories');
        Schema::dropIfExists('card_sort_sessions');
    }
};
