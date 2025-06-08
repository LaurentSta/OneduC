<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Précise le rôle dans le groupe : stagiaire ou formateur (utile si un formateur gère plusieurs groupes)
            $table->enum('role_in_group', ['stagiaire', 'formateur'])->default('stagiaire');

            $table->timestamps();
            $table->unique(['group_id', 'user_id']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_user');
    }
};
