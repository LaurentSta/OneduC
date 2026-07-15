<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        DB::table('categories')
            ->where('category_slug', 'modules-formateurs')
            ->where('category_name', 'Modules formateurs')
            ->update(['category_name' => 'Formations formateurs']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        DB::table('categories')
            ->where('category_slug', 'modules-formateurs')
            ->where('category_name', 'Formations formateurs')
            ->update(['category_name' => 'Modules formateurs']);
    }
};
