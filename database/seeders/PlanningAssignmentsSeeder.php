<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanningAssignment;
use App\Models\Employee;
use App\Models\PlanningModel;
use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;

class PlanningAssignmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $planningModels = PlanningModel::all();
        $campaigns = Campaign::all();
        
        if ($employees->isEmpty() || $planningModels->isEmpty() || $campaigns->isEmpty()) {
            return;
        }

        // Affectations de planning aux superviseurs
        $supEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'SUP';
        });
        
        foreach ($supEmployees as $sup) {
            // Un superviseur peut avoir plusieurs plannings dans le temps
            for ($i = 1; $i <= 2; $i++) {
                $planningModel = $planningModels->random();
                $startDate = now()->addDays(rand(1, 30));
                $endDate = now()->addDays(rand(60, 180));
                
                PlanningAssignment::create([
                    'employee_id' => $sup->id,
                    'planning_model_id' => $planningModel->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'validé',
                ]);
            }
        }

        // Affectations de planning aux téléconseillers
        $tcEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'TC';
        });
        
        foreach ($tcEmployees as $tc) {
            // Un téléconseiller peut avoir plusieurs plannings dans le temps
            for ($i = 1; $i <= 2; $i++) {
                $planningModel = $planningModels->random();
                $startDate = now()->addDays(rand(1, 30));
                $endDate = now()->addDays(rand(60, 180));
                
                PlanningAssignment::create([
                    'employee_id' => $tc->id,
                    'planning_model_id' => $planningModel->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => 'validé',
                ]);
            }
        }

        // Créer quelques affectations en attente pour démonstration
        for ($i = 0; $i < 10; $i++) {
            $employee = $employees->random();
            $planningModel = $planningModels->random();
            $startDate = now()->addDays(rand(1, 30));
            
            PlanningAssignment::create([
                'employee_id' => $employee->id,
                'planning_model_id' => $planningModel->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => null,
                'status' => 'en attente',
            ]);
        }
    }
}