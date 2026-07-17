<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * doctrine/dbal n'est pas installé : on modifie la colonne en SQL brut
     * plutôt que via Schema::table(...)->change().
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE poll_sessions MODIFY group_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE poll_sessions MODIFY group_id BIGINT UNSIGNED NOT NULL');
    }
};
