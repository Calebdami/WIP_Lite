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
        // On récupère le rôle admin
        $adminRole = Role::where('name', 'admin')->first();

        User::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Utilise un mot de passe sécurisé en prod
            'role_id' => $adminRole->id,
        ]);
    }
}