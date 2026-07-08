<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->longText('html_content')->nullable()->after('scorm_path');
        });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->dropColumn('html_content');
        });
    }
};
