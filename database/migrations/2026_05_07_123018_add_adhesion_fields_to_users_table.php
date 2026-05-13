<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'adhesion_status')) {
                $table->string('adhesion_status', 20)->default('pending')->after('status');
            }

            if (!Schema::hasColumn('users', 'adhesion_valid_until')) {
                $table->date('adhesion_valid_until')->nullable()->after('adhesion_status');
            }

            if (!Schema::hasColumn('users', 'adhesion_verified_at')) {
                $table->timestamp('adhesion_verified_at')->nullable()->after('adhesion_valid_until');
            }

            if (!Schema::hasColumn('users', 'adhesion_verified_by')) {
                $table->foreignId('adhesion_verified_by')
                    ->nullable()
                    ->after('adhesion_verified_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'adhesion_verified_by')) {
                $table->dropConstrainedForeignId('adhesion_verified_by');
            }

            foreach (['adhesion_verified_at', 'adhesion_valid_until', 'adhesion_status'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
