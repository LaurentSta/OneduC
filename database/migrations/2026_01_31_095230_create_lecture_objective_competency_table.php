<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_objective_competency', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lecture_objective_id')
                ->constrained('lecture_objectives')
                ->cascadeOnDelete();

            $table->foreignId('competency_id')
                ->constrained('competencies')
                ->cascadeOnDelete();

            $table->unsignedInteger('position')->default(1); // ordre dans l’objectif
            $table->timestamps();

            $table->unique(['lecture_objective_id', 'competency_id'], 'u_obj_comp');
            $table->index(['lecture_objective_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_objective_competency');
    }
};
