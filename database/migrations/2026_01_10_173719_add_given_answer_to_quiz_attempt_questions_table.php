<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('quiz_attempt_questions', function (Blueprint $table) {
        $table->json('given_answer')->nullable()->after('question_id');
    });
}

public function down(): void
{
    Schema::table('quiz_attempt_questions', function (Blueprint $table) {
        $table->dropColumn('given_answer');
    });
}

};
