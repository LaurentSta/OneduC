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
        Schema::table('group_whiteboards', function (Blueprint $table) {
            $table->json('excalidraw_data')->nullable()->after('settings');
        });
    }

    public function down(): void
    {
        Schema::table('group_whiteboards', function (Blueprint $table) {
            $table->dropColumn('excalidraw_data');
        });
    }
};
