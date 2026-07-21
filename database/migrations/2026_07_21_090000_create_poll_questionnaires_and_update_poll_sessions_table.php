<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_questionnaires', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->json('questions');
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
        });

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

        Schema::dropIfExists('poll_questionnaires');
    }
};
