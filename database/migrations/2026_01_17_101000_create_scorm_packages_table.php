<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scorm_packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');              // Nom affiché (ex : Branchement)
            $table->string('slug')->unique();    // Dossier racine (ex : 01_branchement)

            $table->unsignedBigInteger('active_version_id')->nullable(); // version active (dernière importée)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_packages');
    }
};
