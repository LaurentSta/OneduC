<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table): void {
            if (! Schema::hasColumn('module_lectures', 'live_quiz_entry_enabled')) {
                $table->boolean('live_quiz_entry_enabled')
                    ->default(false)
                    ->after('quiz_questions_per_attempt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table): void {
            if (Schema::hasColumn('module_lectures', 'live_quiz_entry_enabled')) {
                $table->dropColumn('live_quiz_entry_enabled');
            }
        });
    }
};
