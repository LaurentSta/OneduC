<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorm_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lecture_id')->constrained('module_lectures')->onDelete('cascade');

            $table->string('scorm_key');   // ex: cmi.core.score.raw
            $table->text('scorm_value');   // ex: 100, incomplete, etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_results');
    }
};
