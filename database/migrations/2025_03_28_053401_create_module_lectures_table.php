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
         Schema::create('module_lectures', function (Blueprint $table) {
             $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('module_sections')->onDelete('cascade');
             $table->string('lecture_title')->nullable();
             $table->string('video')->nullable();
             $table->string('url')->nullable();
             $table->text('content')->nullable();
             $table->timestamps();
         });
     }

     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists('module_lectures');
     }
 };
