<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            Schema::create('scorm_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('lecture_id')->constrained('module_lectures')->onDelete('cascade');

                $table->unsignedTinyInteger('first_score')->nullable(); // 🧠 Premier essai
                $table->unsignedTinyInteger('best_score')->nullable();  // ✅ Score max

                $table->unsignedSmallInteger('attempts_count')->default(1); // 🔁 Tentatives (relances de la leçon)
                $table->unsignedSmallInteger('questions_answered')->nullable(); // ✅ Nombre réel de questions répondues

                $table->unsignedTinyInteger('last_score')->nullable();      // 🕹️ Dernier score
                $table->timestamp('last_attempt_at')->nullable();           // 🕓 Dernière tentative

                $table->boolean('is_completed')->default(false); // 🎯 Terminé si score ≥ 75

                $table->timestamps();

                $table->unique(['user_id', 'lecture_id']); // ✔️ Une seule ligne par combinaison
            });

        }

    public function down(): void
    {
        Schema::dropIfExists('scorm_scores');
    }
};

