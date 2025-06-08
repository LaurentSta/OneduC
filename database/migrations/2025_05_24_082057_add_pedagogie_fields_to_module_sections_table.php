<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('module_sections', function (Blueprint $table) {
            $table->text('objectif')->nullable()->after('section_title');
            $table->text('methode')->nullable()->after('objectif');
            $table->text('contexte')->nullable()->after('methode');
            $table->string('scorm_video_path')->nullable()->after('contexte');
        });
    }

    public function down(): void
    {
        Schema::table('module_sections', function (Blueprint $table) {
            $table->dropColumn(['objectif', 'methode', 'contexte', 'scorm_video_path']);
        });
    }
};
