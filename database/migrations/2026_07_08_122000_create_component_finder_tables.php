<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_finder_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('image_path', 255);
            $table->json('zones');
            $table->string('access_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
            $table->index(['group_id', 'is_active']);
        });

        Schema::create('component_finder_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('component_finder_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('total');
            $table->unsignedInteger('duration_seconds');
            $table->json('details');
            $table->timestamps();

            $table->unique(
                ['component_finder_session_id', 'user_id'],
                'cf_attempts_unique_per_user'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_finder_attempts');
        Schema::dropIfExists('component_finder_sessions');
    }
};
