<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_module', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');

            // Empêche l’association multiple d’un même module à un groupe
            $table->unique(['group_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_module');
    }
};
