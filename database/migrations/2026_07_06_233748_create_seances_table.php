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
        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('formateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->enum('creneau', ['matin', 'apres_midi', 'journee', 'soiree'])->default('matin');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->string('titre')->nullable();
            $table->enum('statut', ['planifiee', 'ouverte', 'cloturee'])->default('planifiee');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'date', 'creneau'], 'seances_group_date_creneau_unique');
            $table->index(['group_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
