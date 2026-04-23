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
        Schema::create('random_wheel_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('access_code', 6)->unique();
            $table->json('entries');
            $table->json('picks')->nullable();
            $table->unsignedBigInteger('current_pick_id')->nullable();
            $table->timestamp('spun_at')->nullable();
            $table->timestamps();

            $table->index(['formateur_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('random_wheel_sessions');
    }
};
