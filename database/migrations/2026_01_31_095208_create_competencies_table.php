<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->nullable()->unique(); // optionnel (ex: EXCEL_001)
            $table->string('label', 255);                     // intitulé
            $table->text('description')->nullable();          // optionnel
            $table->boolean('is_active')->default(true);      // pour masquer sans supprimer
            $table->timestamps();

            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competencies');
    }
};
