<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_whiteboards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->unique()->constrained('groups')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'updated_at']);
        });

        Schema::create('group_whiteboard_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_whiteboard_id')->constrained('group_whiteboards')->cascadeOnDelete();
            $table->string('client_uuid', 80);
            $table->string('type', 32);
            $table->decimal('x', 10, 2)->default(0);
            $table->decimal('y', 10, 2)->default(0);
            $table->decimal('width', 10, 2)->default(280);
            $table->decimal('height', 10, 2)->default(180);
            $table->decimal('rotation', 8, 2)->default(0);
            $table->unsignedInteger('z_index')->default(0);
            $table->json('payload')->nullable();
            $table->json('style')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['group_whiteboard_id', 'client_uuid'],
                'group_whiteboard_items_board_uuid_unique'
            );
            $table->index(['group_whiteboard_id', 'z_index'], 'group_whiteboard_items_board_z_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_whiteboard_items');
        Schema::dropIfExists('group_whiteboards');
    }
};
