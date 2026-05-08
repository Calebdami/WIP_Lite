<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrateur',
            ],
            [
                'name' => 'Chef de Plateau',
            ],
            [ 
                'name' => 'Superviseur',
            ],
            [
                'name' => 'Téléconseiller',
            ],
        ];
        
        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}