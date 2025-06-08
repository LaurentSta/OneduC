<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scorm_evaluation_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();

            $table->string('interaction_id')->nullable();
            $table->string('interaction_type')->nullable();
            $table->string('interaction_weighting')->nullable();
            $table->string('result')->nullable();
            $table->string('response')->nullable();
            $table->string('correct_response')->nullable();
            $table->string('latency')->nullable();
            $table->string('time')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorm_evaluation_interactions');
    }
};
