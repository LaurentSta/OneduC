<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('formateur_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stagiaire_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('sent_as_notification')->default(false);
            $table->boolean('sent_as_email')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formateur_messages');
    }
};
