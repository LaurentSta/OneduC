<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'prenom' => 'Super',
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('111'),
                'role' => 'admin',
                'status' => 1,
            ]
        );

        // FORMATEUR
        User::updateOrCreate(
            ['email' => 'instructor@gmail.com'],
            [
                'prenom' => 'Jean',
                'name' => 'Formateur',
                'username' => 'formateur',
                'password' => Hash::make('111'),
                'role' => 'formateur',
                'status' => 1,
            ]
        );

        // STAGIAIRE
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'prenom' => 'Laura',
                'name' => 'Stagiaire',
                'username' => 'stagiaire',
                'password' => Hash::make('111'),
                'role' => 'stagiaire',
                'status' => 1,
            ]
        );
    }
}