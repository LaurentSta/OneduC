<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_cloud_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_cloud_id')->constrained('word_clouds')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('answer', 150);
            $table->string('normalized_answer', 150)->index();
            $table->timestamps();

            $table->index(['word_cloud_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_cloud_entries');
    }
};
