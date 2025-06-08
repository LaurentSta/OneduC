<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_feedbacks', function (Blueprint $table) {
            $table->string('type')->nullable()->after('comment'); // bug, erreur, etc.
            $table->text('response')->nullable()->after('type'); // réponse admin
            $table->enum('status', ['en_attente', 'traite'])->default('en_attente')->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['type', 'response', 'status']);
        });
    }

};
