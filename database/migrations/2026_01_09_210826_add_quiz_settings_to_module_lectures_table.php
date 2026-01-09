<?php

// database/migrations/xxxx_add_quiz_settings_to_module_lectures_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('module_lectures', function (Blueprint $table) {
      $table->boolean('quiz_enabled')->default(false)->after('question_count');
      $table->unsignedInteger('quiz_questions_per_attempt')->default(0)->after('quiz_enabled');
    });
  }

  public function down(): void {
    Schema::table('module_lectures', function (Blueprint $table) {
      $table->dropColumn(['quiz_enabled','quiz_questions_per_attempt']);
    });
  }
};
