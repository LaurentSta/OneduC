<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->json('pairs');
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('memory_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('memory_session_id')->constrained('memory_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('moves');
            $table->unsignedSmallInteger('errors');
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();

            $table->unique(['memory_session_id', 'user_id'], 'memory_attempts_unique_per_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_attempts');
        Schema::dropIfExists('memory_sessions');
    }
};
