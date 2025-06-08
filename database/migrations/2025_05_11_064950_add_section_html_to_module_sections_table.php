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
        Schema::table('module_sections', function (Blueprint $table) {
            $table->longText('section_html')->nullable();
        });
    }

    public function down()
    {
        Schema::table('module_sections', function (Blueprint $table) {
            $table->dropColumn('section_html');
        });
    }

};
