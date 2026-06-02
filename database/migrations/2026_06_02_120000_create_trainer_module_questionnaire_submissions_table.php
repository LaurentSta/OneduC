<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_module_questionnaire_submissions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('submission_uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('module_number');
            $table->string('module_key');
            $table->string('questionnaire_key');
            $table->unsignedSmallInteger('questionnaire_version')->default(1);
            $table->json('responses');
            $table->timestamp('submitted_at');
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();

            $table->index(['module_number', 'questionnaire_key'], 'tmqs_module_questionnaire_index');
            $table->index(['user_id', 'module_key'], 'tmqs_user_module_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_module_questionnaire_submissions');
    }
};
