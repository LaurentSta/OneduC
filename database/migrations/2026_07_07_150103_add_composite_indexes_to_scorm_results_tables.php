<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scorm_results', function (Blueprint $table) {
            $table->index(['user_id', 'lecture_id', 'scorm_key'], 'scorm_results_user_lecture_key_index');
        });

        Schema::table('content_block_scorm_results', function (Blueprint $table) {
            $table->index(['user_id', 'lecture_id', 'content_block_key', 'scorm_key'], 'cb_scorm_results_user_lecture_key_index');
        });
    }

    public function down(): void
    {
        Schema::table('scorm_results', function (Blueprint $table) {
            $table->dropIndex('scorm_results_user_lecture_key_index');
        });

        Schema::table('content_block_scorm_results', function (Blueprint $table) {
            $table->dropIndex('cb_scorm_results_user_lecture_key_index');
        });
    }
};
