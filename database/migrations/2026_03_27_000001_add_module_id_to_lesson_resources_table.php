<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->foreignId('module_id')
                ->nullable()
                ->after('lecture_id')
                ->constrained('modules')
                ->cascadeOnDelete();
        });

        DB::table('lesson_resources')
            ->join('module_lectures', 'module_lectures.id', '=', 'lesson_resources.lecture_id')
            ->select('lesson_resources.id', 'module_lectures.module_id')
            ->orderBy('lesson_resources.id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('lesson_resources')
                        ->where('id', $row->id)
                        ->update(['module_id' => $row->module_id]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('lesson_resources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('module_id');
        });
    }
};
