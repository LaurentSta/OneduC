<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->json('wc_questions')->nullable()->after('wc_question');
            $table->dropColumn('wc_question');
        });
    }

    public function down(): void
    {
        Schema::table('formateur_parcours_items', function (Blueprint $table) {
            $table->text('wc_question')->nullable()->after('wc_title');
            $table->dropColumn('wc_questions');
        });
    }
};
