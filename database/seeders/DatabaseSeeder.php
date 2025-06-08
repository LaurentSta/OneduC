<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ ADMIN
        User::create([
            'prenom' => 'Super',
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('111'),
            'role' => 'admin',
            'status' => true,
        ]);

        // ✅ FORMATEUR
        User::create([
            'prenom' => 'Jean',
            'name' => 'Formateur',
            'username' => 'formateur',
            'email' => 'instructor@gmail.com',
            'password' => Hash::make('111'),
            'role' => 'formateur',
            'status' => true,
        ]);

        // ✅ STAGIAIRE
        User::create([
            'prenom' => 'Laura',
            'name' => 'Stagiaire',
            'username' => 'stagiaire',
            'email' => 'user@gmail.com',
            'password' => Hash::make('111'),
            'role' => 'stagiaire',
            'status' => true,
        ]);
    }
}
