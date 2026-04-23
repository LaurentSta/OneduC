<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('random_wheel_sessions', function (Blueprint $table): void {
            $table->json('active_entry_ids')->nullable()->after('entries');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('random_wheel_sessions', 'active_entry_ids')) {
            return;
        }

        Schema::table('random_wheel_sessions', function (Blueprint $table): void {
            $table->dropColumn('active_entry_ids');
        });
    }
};
