<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('group_module', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('module_id');
        });

        // Optionnel : remplir un ordre initial (par id)
        $rows = DB::table('group_module')->orderBy('group_id')->orderBy('id')->get(['id','group_id']);
        $currentGroup = null;
        $pos = 0;

        foreach ($rows as $r) {
            if ($currentGroup !== $r->group_id) { $currentGroup = $r->group_id; $pos = 0; }
            $pos++;
            DB::table('group_module')->where('id', $r->id)->update(['position' => $pos]);
        }
    }

    public function down(): void
    {
        Schema::table('group_module', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};

