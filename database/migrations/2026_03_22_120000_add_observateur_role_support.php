<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('stagiaire', 'formateur', 'admin', 'observateur') NOT NULL DEFAULT 'stagiaire'");
            DB::statement("ALTER TABLE group_user MODIFY COLUMN role_in_group ENUM('stagiaire', 'formateur', 'observateur') NOT NULL DEFAULT 'stagiaire'");
        }
    }

    public function down(): void
    {
        DB::table('group_user')
            ->where('role_in_group', 'observateur')
            ->delete();

        DB::table('users')
            ->where('role', 'observateur')
            ->delete();

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('stagiaire', 'formateur', 'admin') NOT NULL DEFAULT 'stagiaire'");
            DB::statement("ALTER TABLE group_user MODIFY COLUMN role_in_group ENUM('stagiaire', 'formateur') NOT NULL DEFAULT 'stagiaire'");
        }
    }
};
