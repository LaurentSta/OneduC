<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_module_lectures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained('module_lectures')->cascadeOnDelete();

            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->unique(['group_id', 'module_id', 'lecture_id'], 'gml_unique');
            $table->index(['group_id', 'module_id'], 'gml_group_module_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_module_lectures');
    }
};
