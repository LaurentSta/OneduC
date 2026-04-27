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
        Schema::create('group_timers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->unique()->constrained('groups')->cascadeOnDelete();
            $table->string('label', 100)->nullable();
            $table->unsignedInteger('duration_seconds')->default(1500);
            $table->enum('status', ['idle', 'running', 'paused', 'finished'])->default('idle');
            $table->timestamp('started_at')->nullable();
            $table->unsignedInteger('elapsed_seconds')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_timers');
    }
};
