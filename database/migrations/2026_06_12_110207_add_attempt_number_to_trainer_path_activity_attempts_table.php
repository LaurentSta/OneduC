<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trainer_path_activity_attempts', function (Blueprint $table) {
            $table->unsignedInteger('attempt_number')
                ->default(1)
                ->after('activity_type');
        });

        $counters = [];

        DB::table('trainer_path_activity_attempts')
            ->select('id', 'user_id', 'activity_key')
            ->orderBy('user_id')
            ->orderBy('activity_key')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->chunk(500, function ($attempts) use (&$counters): void {
                foreach ($attempts as $attempt) {
                    $counterKey = $attempt->user_id.'|'.$attempt->activity_key;
                    $counters[$counterKey] = ($counters[$counterKey] ?? 0) + 1;

                    DB::table('trainer_path_activity_attempts')
                        ->where('id', $attempt->id)
                        ->update(['attempt_number' => $counters[$counterKey]]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainer_path_activity_attempts', function (Blueprint $table) {
            $table->dropColumn('attempt_number');
        });
    }
};
