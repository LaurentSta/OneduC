<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('word_clouds', 'current_question_index')) {
            Schema::table('word_clouds', function (Blueprint $table): void {
                $table->unsignedTinyInteger('current_question_index')
                    ->default(0)
                    ->after('questions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('word_clouds', 'current_question_index')) {
            Schema::table('word_clouds', function (Blueprint $table): void {
                $table->dropColumn('current_question_index');
            });
        }
    }
};
