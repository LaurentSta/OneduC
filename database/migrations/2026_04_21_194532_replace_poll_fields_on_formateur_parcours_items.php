<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->json('poll_questions')->nullable()->after('wc_duration');
            $table->unsignedSmallInteger('poll_duration')->nullable()->after('poll_questions');
            $table->dropColumn(['poll_question', 'poll_choices']);
        });
    }

    public function down(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->string('poll_question', 500)->nullable()->after('wc_duration');
            $table->json('poll_choices')->nullable()->after('poll_question');
            $table->dropColumn(['poll_questions', 'poll_duration']);
        });
    }
};
