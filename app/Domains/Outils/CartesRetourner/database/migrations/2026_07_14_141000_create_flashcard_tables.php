<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcard_sessions', function (Blueprint $table): void {
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

        Schema::create('flashcard_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('flashcard_session_id')->constrained('flashcard_sessions')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->text('recto_text')->nullable();
            $table->string('recto_image_path', 255)->nullable();
            $table->text('verso_text')->nullable();
            $table->string('verso_image_path', 255)->nullable();
            $table->timestamps();

            $table->index(['flashcard_session_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_cards');
        Schema::dropIfExists('flashcard_sessions');
    }
};
