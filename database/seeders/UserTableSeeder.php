<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('111111'),
            'role' => 'admin',
            'societe' => 'Admin Corp', // ✅ Ajout du champ société (optionnel)
            'status' => true, // ✅ Remplacé '1' par true pour le boolean
        ]);

        User::create([
            'name' => 'Instructor',
            'username' => 'instructor',
            'email' => 'instructor@gmail.com',
            'password' => Hash::make('111111'),
            'role' => 'instructor',
            'societe' => 'Formation Academy', // ✅ Exemple de société
            'status' => true, // ✅ Boolean
        ]);

        User::create([
            'name' => 'User',
            'username' => 'user',
            'email' => 'user@gmail.com',
            'password' => Hash::make('111111'),
            'role' => 'user',
            'societe' => 'Indépendant', // ✅ Optionnel
            'status' => true, // ✅ Boolean
        ]);
    }
}
