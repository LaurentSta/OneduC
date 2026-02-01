<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->nullable()->unique(); // ex: BADGE_WORD_BASE
            $table->string('label', 255);
            $table->text('description')->nullable();

            // optionnel : image/visuel du badge (chemin)
            $table->string('image_path', 255)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
