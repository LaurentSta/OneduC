<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formateur_parcours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('formateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formateur_parcours');
    }
};
