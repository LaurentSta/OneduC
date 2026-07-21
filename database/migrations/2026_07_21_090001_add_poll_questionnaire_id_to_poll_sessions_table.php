<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_sessions', function (Blueprint $table): void {
            $table->foreignId('poll_questionnaire_id')
                ->nullable()
                ->after('formateur_id')
                ->constrained('poll_questionnaires')
                ->cascadeOnDelete();
        });

        Schema::table('poll_sessions', function (Blueprint $table): void {
            $table->dropColumn(['title', 'questions']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_sessions', function (Blueprint $table): void {
            $table->string('title', 255)->nullable();
            $table->json('questions')->nullable();
        });

        Schema::table('poll_sessions', function (Blueprint $table): void {
            $table->dropForeign(['poll_questionnaire_id']);
            $table->dropColumn('poll_questionnaire_id');
        });
    }
};
