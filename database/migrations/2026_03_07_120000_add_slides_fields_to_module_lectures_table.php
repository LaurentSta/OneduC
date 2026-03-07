<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('module_lectures', 'content_type')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->string('content_type', 20)->default('scorm')->after('scorm_path');
            });
        }

        if (!Schema::hasColumn('module_lectures', 'slides_status')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->string('slides_status', 20)->default('none')->after('content_type');
            });
        }

        if (!Schema::hasColumn('module_lectures', 'slides_path')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->string('slides_path')->nullable()->after('slides_status');
            });
        }

        if (!Schema::hasColumn('module_lectures', 'slides_error')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->text('slides_error')->nullable()->after('slides_path');
            });
        }

        if (!Schema::hasColumn('module_lectures', 'slides_converted_at')) {
            Schema::table('module_lectures', function (Blueprint $table) {
                $table->timestamp('slides_converted_at')->nullable()->after('slides_error');
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'content_type',
            'slides_status',
            'slides_path',
            'slides_error',
            'slides_converted_at',
        ];

        $existing = array_filter($columns, fn (string $column) => Schema::hasColumn('module_lectures', $column));

        if (!empty($existing)) {
            Schema::table('module_lectures', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
