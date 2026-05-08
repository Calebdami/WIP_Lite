<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // On récupère les rôles
        $adminRole = Role::where('name', 'Administrateur')->first();
        $cpRole = Role::where('name', 'Chef de Plateau')->first();
        $supRole = Role::where('name', 'Superviseur')->first();
        $tcRole = Role::where('name', 'Téléconseiller')->first();

        // Créer 10 utilisateurs avec des IDs 1-10
        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'email' => 'manager1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $cpRole->id,
        ]);

        User::create([
            'email' => 'manager2@example.com',
            'password' => Hash::make('password'),
            'role_id' => $cpRole->id,
        ]);

        User::create([
            'email' => 'supervisor1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $supRole->id,
        ]);

        User::create([
            'email' => 'supervisor2@example.com',
            'password' => Hash::make('password'),
            'role_id' => $supRole->id,
        ]);

        User::create([
            'email' => 'tech1@example.com',
            'password' => Hash::make('password'),
            'role_id' => $tcRole->id,
        ]);

        User::create([
            'email' => 'tech2@example.com',
            'password' => Hash::make('password'),
            'role_id' => $tcRole->id,
        ]);

        User::create([
            'email' => 'tech3@example.com',
            'password' => Hash::make('password'),
            'role_id' => $tcRole->id,
        ]);

        User::create([
            'email' => 'tech4@example.com',
            'password' => Hash::make('password'),
            'role_id' => $tcRole->id,
        ]);

        User::create([
            'email' => 'tech5@example.com',
            'password' => Hash::make('password'),
            'role_id' => $tcRole->id,
        ]);
    }
}