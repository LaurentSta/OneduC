<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInteractionKeyToScormInteractionsTable extends Migration
{
    public function up()
    {
        Schema::table('scorm_interactions', function (Blueprint $table) {
            $table->string('interaction_key')->nullable()->after('interaction_id');

            // Index unique pour éviter les doublons d’une même question par le même user dans la même leçon
            $table->unique(['user_id', 'lecture_id', 'interaction_key'], 'unique_scorm_interaction_key');
        });
    }

    public function down()
    {
        Schema::table('scorm_interactions', function (Blueprint $table) {
            $table->dropUnique('unique_scorm_interaction_key');
            $table->dropColumn('interaction_key');
        });
    }
}
