<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('module_lectures', 'slides_source_path')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->string('slides_source_path')->nullable()->after('slides_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('module_lectures', 'slides_source_path')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->dropColumn('slides_source_path');
            });
        }
    }
};
