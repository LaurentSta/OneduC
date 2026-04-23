<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('formateur_parcours_modules');

        Schema::create('formateur_parcours_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_parcours_id')
                ->constrained('formateur_parcours')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('type', 20); // 'module' | 'wordcloud'
            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();
            $table->string('wc_title', 255)->nullable();
            $table->text('wc_question')->nullable();
            $table->timestamps();

            $table->index(['formateur_parcours_id', 'position'], 'parcours_items_position_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formateur_parcours_items');

        Schema::create('formateur_parcours_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_parcours_id')
                ->constrained('formateur_parcours')
                ->cascadeOnDelete();
            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['formateur_parcours_id', 'module_id'], 'parcours_module_unique');
            $table->index(['formateur_parcours_id', 'position'], 'parcours_position_idx');
        });
    }
};
