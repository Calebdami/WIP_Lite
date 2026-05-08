<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $campaigns = Campaign::all();
        $positions = Position::all();

        if ($employees->isEmpty() || $campaigns->isEmpty() || $positions->isEmpty()) {
            return;
        }

        // Affectations des Chefs de Plateau (CP) aux campagnes
        $cpEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'CP';
        });
        
        foreach ($cpEmployees as $cp) {
            // Un CP peut être affecté à plusieurs campagnes
            $activeCampaigns = $campaigns->where('status', 'active')->random(2);
            
            foreach ($activeCampaigns as $campaign) {
                Assignment::create([
                    'employee_id' => $cp->id,
                    'campaign_id' => $campaign->id,
                    'position_id' => $positions->where('code', 'CP')->first()->id,
                    'manager_id' => null, // CP n'a pas de manager
                    'status' => 'active',
                    'start_date' => now()->subDays(rand(30, 180))->format('Y-m-d'),
                    'end_date' => null,
                ]);
            }
        }

        // Affectations des Superviseurs (SUP) aux CPs
        $supEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'SUP';
        });
        
        foreach ($supEmployees as $sup) {
            // Un SUP est affecté à un seul CP et une seule campagne
            $cpAssignment = Assignment::where('position_id', $positions->where('code', 'CP')->first()->id)
                ->where('status', 'active')
                ->whereNotNull('employee_id')
                ->inRandomOrder()
                ->first();
            
            if ($cpAssignment) {
                Assignment::create([
                    'employee_id' => $sup->id,
                    'campaign_id' => $cpAssignment->campaign_id,
                    'position_id' => $positions->where('code', 'SUP')->first()->id,
                    'manager_id' => $cpAssignment->id, // Référence à l'assignment du CP
                    'status' => 'active',
                    'start_date' => now()->subDays(rand(15, 90))->format('Y-m-d'),
                    'end_date' => null,
                ]);
            }
        }

        // Affectations des Téléconseillers (TC) aux SUPs
        $tcEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'TC';
        });
        
        foreach ($tcEmployees as $tc) {
            // Un TC est affecté à un seul SUP et hérite de la campagne
            $supAssignment = Assignment::where('position_id', $positions->where('code', 'SUP')->first()->id)
                ->where('status', 'validé')
                ->whereNotNull('employee_id')
                ->inRandomOrder()
                ->first();
            
            if ($supAssignment) {
                Assignment::create([
                    'employee_id' => $tc->id,
                    'campaign_id' => $supAssignment->campaign_id,
                    'position_id' => $positions->where('code', 'TC')->first()->id,
                    'manager_id' => $supAssignment->id, // Référence à l'assignment du SUP
                    'status' => 'active',
                    'start_date' => now()->subDays(rand(7, 30))->format('Y-m-d'),
                    'end_date' => null,
                ]);
            }
        }

        // Créer quelques affectations en attente pour démonstration
        for ($i = 0; $i < 20; $i++) {
            $employee = $employees->random();
            $campaign = $campaigns->where('status', 'active')->first();
            $position = $positions->random();
            
            Assignment::create([
                'employee_id' => $employee->id,
                'campaign_id' => $campaign->id,
                'position_id' => $position->id,
                'manager_id' => null,
                'status' => 'suspended',
                'start_date' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                'end_date' => null,
            ]);
        }
    }
}