<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('word_clouds', function (Blueprint $table) {
            $table->foreignId('formateur_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Rétro-remplissage : les nuages existants sont tous rattachés à un groupe.
        DB::statement(
            'UPDATE word_clouds wc
             INNER JOIN groups g ON g.id = wc.group_id
             SET wc.formateur_id = g.instructor_id
             WHERE wc.formateur_id IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('word_clouds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('formateur_id');
        });
    }
};
