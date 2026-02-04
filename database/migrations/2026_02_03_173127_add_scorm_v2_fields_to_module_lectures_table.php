<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->string('scorm_folder')->nullable()->after('scorm_path');
            $table->string('scorm_launch_path')->nullable()->after('scorm_folder');
        });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->dropColumn(['scorm_launch_path', 'scorm_folder']);
        });
    }
};
