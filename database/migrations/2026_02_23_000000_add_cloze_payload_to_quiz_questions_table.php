<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quiz_questions')) {
            return;
        }

        if (!Schema::hasColumn('quiz_questions', 'payload')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->json('payload')->nullable()->after('question_text');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN type ENUM('single','multiple','boolean','cloze') NOT NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('quiz_questions')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN type ENUM('single','multiple','boolean') NOT NULL");
        }

        if (Schema::hasColumn('quiz_questions', 'payload')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->dropColumn('payload');
            });
        }
    }
};
