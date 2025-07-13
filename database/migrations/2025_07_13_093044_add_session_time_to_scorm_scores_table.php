<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
    {
        Schema::table('scorm_scores', function (Blueprint $table) {
            $table->unsignedInteger('session_time')->default(0);

        });
    }

    public function down()
    {
        Schema::table('scorm_scores', function (Blueprint $table) {
            $table->dropColumn('session_time');
        });
    }

};
