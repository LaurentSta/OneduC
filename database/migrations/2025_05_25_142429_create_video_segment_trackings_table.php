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
        Schema::create('video_segment_trackings', function (Blueprint $table) {
            $table->id();

            // Liens avec les tables existantes
            $table->foreignId('user_id')->constrained();
            $table->foreignId('lecture_id')->constrained('module_lectures')->onDelete('cascade');

            // Suivi vidéo
            $table->integer('segment_start'); // en secondes (ex: 0, 30, 60)
            $table->integer('segment_end');   // en secondes (ex: 30, 60, 90)
            $table->integer('watch_count')->default(0);
            $table->float('total_watch_time')->default(0); // durée cumulée

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_segment_trackings');
    }
};
