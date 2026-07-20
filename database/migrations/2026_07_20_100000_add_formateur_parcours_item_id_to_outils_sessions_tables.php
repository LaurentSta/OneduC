<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'buzzer_sessions',
        'scale_sessions',
        'true_false_sessions',
        'random_wheel_sessions',
        'component_finder_sessions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->foreignId('formateur_parcours_item_id')
                    ->nullable()
                    ->after('group_id')
                    ->constrained('formateur_parcours_items')
                    ->nullOnDelete();

                $blueprint->unique(
                    ['formateur_parcours_item_id', 'group_id'],
                    $table.'_parcours_item_group_unique'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique($table.'_parcours_item_group_unique');
                $blueprint->dropConstrainedForeignId('formateur_parcours_item_id');
            });
        }
    }
};
