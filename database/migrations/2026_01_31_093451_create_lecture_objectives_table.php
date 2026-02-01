<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_objectives', function (Blueprint $table) {
            $table->id();

            // Lien direct vers la leçon (module_lectures.id)
            $table->foreignId('lecture_id')
                ->constrained('module_lectures')
                ->cascadeOnDelete();

            // Objectif (court) + détail optionnel
            $table->string('title', 255);
            $table->text('description')->nullable();

            // Ordre d’affichage dans la leçon
            $table->unsignedInteger('position')->default(1);

            $table->timestamps();

            // Index utiles
            $table->index(['lecture_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_objectives');
    }
};
