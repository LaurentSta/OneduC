<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->string('poll_question', 500)->nullable()->after('wc_question');
            $table->json('poll_choices')->nullable()->after('poll_question');
        });
    }

    public function down(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->dropColumn(['poll_question', 'poll_choices']);
        });
    }
};
