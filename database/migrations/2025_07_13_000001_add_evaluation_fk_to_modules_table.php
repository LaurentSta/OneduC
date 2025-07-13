<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->foreignId('evaluation_id')->nullable()->after('formateur_id')->constrained('evaluations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['evaluation_id']);
            $table->dropColumn('evaluation_id');
        });
    }
};
