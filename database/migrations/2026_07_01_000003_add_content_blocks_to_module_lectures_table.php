<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->json('content_blocks')->nullable()->after('html_content');
        });

        DB::table('module_lectures')
            ->where('content_type', 'html')
            ->orderBy('id')
            ->select('id', 'html_content')
            ->chunkById(200, function ($lectures) {
                foreach ($lectures as $lecture) {
                    $html = trim((string) $lecture->html_content);
                    $blocks = $html !== '' ? [['type' => 'text', 'html' => $html]] : [];

                    DB::table('module_lectures')
                        ->where('id', $lecture->id)
                        ->update([
                            'content_blocks' => json_encode($blocks),
                            'content_type' => 'blocks',
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('module_lectures', function (Blueprint $table) {
            $table->dropColumn('content_blocks');
        });
    }
};
