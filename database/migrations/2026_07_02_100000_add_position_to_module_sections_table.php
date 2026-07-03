<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('module_sections', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('module_id');
        });

        DB::table('module_sections')
            ->select('id', 'module_id')
            ->orderBy('module_id')
            ->orderBy('id')
            ->get()
            ->groupBy('module_id')
            ->each(function ($sections) {
                foreach ($sections->values() as $index => $section) {
                    DB::table('module_sections')->where('id', $section->id)->update(['position' => $index]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_sections', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
