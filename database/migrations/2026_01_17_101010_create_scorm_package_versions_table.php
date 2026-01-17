<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scorm_package_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scorm_package_id')
                ->constrained('scorm_packages')
                ->cascadeOnDelete(); // supprimer le paquet supprimera ses versions (si aucune leçon ne l’utilise)

            $table->string('version');      // ex : v0001
            $table->string('folder');       // ex : public/modules/scorm/01_branchement/v0001
            $table->string('index_path');   // ex : public/modules/scorm/01_branchement/v0001/res/index.html

            $table->unsignedBigInteger('size_bytes')->default(0); // taille utile pour tableau

            $table->boolean('api_injected')->default(false); // injection effectuée à l’import
            $table->timestamp('imported_at')->nullable();

            $table->timestamps();

            $table->unique(['scorm_package_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_package_versions');
    }
};
