<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            // Suppressions
            $table->dropColumn('video');
            $table->dropColumn('content');

            // Ajouts
            $table->unsignedInteger('slide_count')->default(0)->after('scorm_path');
            $table->unsignedInteger('question_count')->default(0)->after('slide_count');
        });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->string('video')->nullable();
            $table->text('content')->nullable();
            $table->dropColumn(['slide_count', 'question_count']);
        });
    }
};
