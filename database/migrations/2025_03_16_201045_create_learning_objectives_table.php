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
        Schema::create('module_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('progress')->default(0); // progression en %
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

     }

     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists('module_goals');
     }
 };
