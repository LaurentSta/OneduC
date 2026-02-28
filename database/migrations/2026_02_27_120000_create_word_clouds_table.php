<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_clouds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('title');
            $table->text('question');
            $table->string('access_code', 12)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_clouds');
    }
};
