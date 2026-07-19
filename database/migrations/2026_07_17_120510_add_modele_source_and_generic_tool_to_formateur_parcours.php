<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateur_parcours', function (Blueprint $table): void {
            $table->foreignId('modele_parcours_id')
                ->nullable()
                ->after('formateur_id')
                ->constrained('modeles_parcours')
                ->nullOnDelete();
        });

        Schema::table('formateur_parcours_items', function (Blueprint $table): void {
            $table->string('outil', 64)->nullable()->after('module_id');
            $table->json('configuration')->nullable()->after('outil');
        });
    }

    public function down(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table): void {
            $table->dropColumn(['outil', 'configuration']);
        });

        Schema::table('formateur_parcours', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('modele_parcours_id');
        });
    }
};
