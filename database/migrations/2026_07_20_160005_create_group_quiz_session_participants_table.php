<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_quiz_session_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_quiz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['group_quiz_session_id', 'user_id'], 'group_quiz_participants_unique_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_quiz_session_participants');
    }
};
