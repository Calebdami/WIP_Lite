<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = Position::all();
        $users = User::all();
        
        // Création d'un employé administrateur
        Employee::create([
            'user_id' => $users->where('email', 'admin@example.com')->first()->id,
            'matricule' => 'EMP-001',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'birth_date' => '1990-05-15',
            'hire_date' => '2024-01-01',
            'phone' => '0102030405',
            'email' => 'jean.dupont@entreprise.com',
            'address' => '123 Rue de la Paix, Paris',
            'position_id' => $positions->where('code', 'ADMIN')->first()->id,
            'salary_base' => 3500.00,
            'status' => 'actif',
        ]);
        
        // Création d'un Chef de Plateau
        Employee::create([
            'user_id' => $users->where('email', 'manager1@example.com')->first()->id,
            'matricule' => 'EMP-002',
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'birth_date' => '1985-03-20',
            'hire_date' => '2023-06-15',
            'phone' => '0102030406',
            'email' => 'marie.martin@entreprise.com',
            'address' => '456 Avenue des Champs-Élysées, Paris',
            'position_id' => $positions->where('code', 'CP')->first()->id,
            'salary_base' => 250000.00,
            'status' => 'actif',
        ]);
        
        // Création des Superviseurs existants
        $supervisorEmails = ['supervisor1@example.com', 'supervisor2@example.com'];
        foreach ($supervisorEmails as $index => $email) {
            $user = $users->where('email', $email)->first();
            if ($user) {
                Employee::create([
                    'user_id' => $user->id,
                    'matricule' => 'EMP-SUP-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'birth_date' => fake()->date('Y-m-d', '1980-01-01'),
                    'hire_date' => fake()->date('Y-m-d', '2023-01-01'),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->unique()->companyEmail(),
                    'address' => fake()->address(),
                    'position_id' => $positions->where('code', 'SUP')->first()->id,
                    'salary_base' => 180000.00,
                    'status' => 'actif',
                ]);
            }
        }
        
        // Création des Téléconseillers existants
        $techEmails = ['tech1@example.com', 'tech2@example.com', 'tech3@example.com', 'tech4@example.com', 'tech5@example.com'];
        foreach ($techEmails as $index => $email) {
            $user = $users->where('email', $email)->first();
            if ($user) {
                Employee::create([
                    'user_id' => $user->id,
                    'matricule' => 'EMP-TC-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'birth_date' => fake()->date('Y-m-d', '1990-01-01'),
                    'hire_date' => fake()->date('Y-m-d', '2023-01-01'),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->unique()->companyEmail(),
                    'address' => fake()->address(),
                    'position_id' => $positions->where('code', 'TC')->first()->id,
                    'salary_base' => 150000.00,
                    'status' => fake()->randomElement(['actif', 'suspendu']), // Plus de chances d'être suspendu
                ]);
            }
        }
        
        // Création d'employés avec statuts variés
        // Récupérer les utilisateurs qui n'ont pas encore d'employé
        $usedUserIds = Employee::whereNotNull('user_id')->pluck('user_id')->toArray();
        $availableUsers = $users->whereNotIn('id', $usedUserIds);
        
        $userIndex = 0;
        for ($i = 10; $i <= 60; $i++) {
            $userId = null;
            
            // Assigner un utilisateur disponible si possible
            if ($userIndex < $availableUsers->count()) {
                $user = $availableUsers->get($userIndex);
                if ($user) {
                    $userId = $user->id;
                    $userIndex++;
                }
            }
            
            Employee::create([
                'user_id' => $userId,
                'matricule' => 'EMP-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'birth_date' => fake()->date('Y-m-d', '1975-01-01'),
                'hire_date' => fake()->date('Y-m-d', '2022-01-01'),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->unique()->companyEmail(),
                'address' => fake()->address(),
                'position_id' => $positions->random()->id,
                'salary_base' => fake()->randomFloat(2, 120000, 250000),
                'status' => fake()->randomElement(['actif', 'inactif', 'suspendu']),
            ]);
        }
    }
}
