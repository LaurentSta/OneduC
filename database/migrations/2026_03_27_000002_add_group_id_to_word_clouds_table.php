<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('word_clouds', function (Blueprint $table): void {
            $table->foreignId('group_id')
                ->nullable()
                ->after('module_id')
                ->constrained('groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('word_clouds', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
