<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('formateur_id')->nullable()->after('id');

        // Si tu veux forcer l'intégrité (pas obligé)
        $table->foreign('formateur_id')->references('id')->on('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['formateur_id']);
        $table->dropColumn('formateur_id');
    });
}
};
