<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modeles_parcours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('auteur_admin_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->timestamp('publie_le')->nullable();
            $table->timestamp('archive_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'publie_le']);
        });

        Schema::create('modele_parcours_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('modele_parcours_id')
                ->constrained('modeles_parcours')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('type', 20);
            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->restrictOnDelete();
            $table->string('outil', 64)->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->index(['modele_parcours_id', 'position'], 'modele_parcours_items_position_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modele_parcours_items');
        Schema::dropIfExists('modeles_parcours');
    }
};
