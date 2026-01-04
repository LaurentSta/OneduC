<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('skill_referential_id')
                ->constrained('skill_referentials')
                ->cascadeOnDelete();

            // domaine optionnel (certains référentiels n’en ont pas)
            $table->foreignId('skill_domain_id')
                ->nullable()
                ->constrained('skill_domains')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['skill_referential_id', 'skill_domain_id', 'position']);
            $table->unique(['skill_referential_id', 'code']); // code unique dans le référentiel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
