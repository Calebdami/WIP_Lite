<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = Position::all();
        $users = User::all();

        // Création d'un employé spécifique (ex: vous)
        Employee::create([
            'user_id' => $users->first()?->id,
            'matricule' => 'EMP-001',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'birth_date' => '1990-05-15',
            'phone' => '0102030405',
            'email' => 'jean.dupont@entreprise.com',
            'address' => '123 Rue de la Paix, Paris',
            'position_id' => $positions->where('code', 'ADMIN')->first()->id,
            'salary_base' => 3500.00,
            'status' => 'actif',
        ]);

        // Génération de 10 employés aléatoires
        for ($i = 2; $i <= 300; $i++) {
            Employee::create([
                'matricule' => 'EMP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'birth_date' => fake()->date('Y-m-d', '2000-01-01'),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->unique()->companyEmail(),
                'address' => fake()->address(),
                'position_id' => $positions->random()->id,
                'salary_base' => fake()->randomFloat(2, 1800, 4500),
                'status' => fake()->randomElement(['actif', 'actif', 'suspendu']), // Plus de chances d'être actif
            ]);
        }
    }
}
