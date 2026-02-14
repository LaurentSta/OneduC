<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            // Ajoute la colonne après 'lecture_title' ou 'position'
            $table->integer('duration')->nullable()->comment('Durée estimée en minutes');
        });
    }

    public function down()
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
