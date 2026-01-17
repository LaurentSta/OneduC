<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->foreignId('scorm_package_id')
                ->nullable()
                ->after('scorm_path')
                ->constrained('scorm_packages')
                ->restrictOnDelete();

            $table->foreignId('scorm_package_version_id')
                ->nullable()
                ->after('scorm_package_id')
                ->constrained('scorm_package_versions')
                ->restrictOnDelete();

            // Par défaut : la leçon suit la version active du paquet
            $table->boolean('use_active_scorm_version')
                ->default(true)
                ->after('scorm_package_version_id');
        });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scorm_package_version_id');
            $table->dropConstrainedForeignId('scorm_package_id');
            $table->dropColumn('use_active_scorm_version');
        });
    }
};
