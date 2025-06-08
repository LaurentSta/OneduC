<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lesson_feedbacks', function (Blueprint $table) {
            $table->softDeletes(); // 👈 ajoute automatiquement la colonne deleted_at (nullable timestamp)
        });
    }

    public function down(): void
    {
        Schema::table('lesson_feedbacks', function (Blueprint $table) {
            $table->dropSoftDeletes(); // supprime la colonne deleted_at
        });
    }
};
