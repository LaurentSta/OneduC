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
        Schema::table('groups', function (Blueprint $table) {
            $table->softDeletes();

            // Une contrainte UNIQUE classique ne peut pas exprimer "unique parmi
            // les lignes non supprimées" en MySQL : un groupe soft-supprimé
            // garderait son nom/code, bloquant leur réutilisation. L'unicité
            // est désormais appliquée côté validation Laravel (->withoutTrashed())
            // ; on garde un index simple pour les performances de recherche.
            $table->dropUnique('groups_name_unique');
            $table->index('name');

            $table->dropUnique('groups_emargement_code_unique');
            $table->index('emargement_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('groups_name_index');
            $table->unique('name');

            $table->dropIndex('groups_emargement_code_index');
            $table->unique('emargement_code');

            $table->dropSoftDeletes();
        });
    }
};
