<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scorm_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lecture_id')->constrained('module_lectures')->onDelete('cascade');

            $table->string('interaction_id')->nullable();
            $table->string('interaction_type')->nullable();
            $table->string('interaction_weighting')->nullable();

            $table->string('result')->nullable();
            $table->string('response')->nullable();
            $table->string('correct_response')->nullable();
            $table->string('latency')->nullable();
            $table->string('time')->nullable();

            $table->string('lesson_status')->nullable(); // 🆕 Statut : completed, incomplete, etc.

            $table->timestamps();

            $table->unique(['user_id', 'lecture_id', 'interaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_interactions');
    }
};
