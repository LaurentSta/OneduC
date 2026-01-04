<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_domains', function (Blueprint $table) {
            $table->id();

            $table->foreignId('skill_referential_id')
                ->constrained('skill_referentials')
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->text('description')->nullable();

            // pour trier proprement dans l’admin
            $table->unsignedInteger('position')->default(0);

            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['skill_referential_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_domains');
    }
};
