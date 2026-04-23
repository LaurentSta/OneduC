<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('formateur_parcours_id')
                ->nullable()
                ->after('instructor_id')
                ->constrained('formateur_parcours')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['formateur_parcours_id']);
            $table->dropColumn('formateur_parcours_id');
        });
    }
};
