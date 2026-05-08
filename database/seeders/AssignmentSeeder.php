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
        
        // Créer 2 CP par campagne active
        $activeCampaigns = $campaigns->where('status', 'active');
        $cpIndex = 0;
        
        foreach ($activeCampaigns as $campaign) {
            // Assigner 2 CP à cette campagne
            for ($i = 0; $i < 2; $i++) {
                if ($cpIndex < $cpEmployees->count()) {
                    $cp = $cpEmployees->get($cpIndex);
                    
                    if ($cp) {
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
                    
                    $cpIndex++;
                }
            }
        }

        // Affectations des Superviseurs (SUP) aux CPs - 3 superviseurs par CP
        $supEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'SUP';
        });
        
        // Récupérer tous les CP actifs
        $cpEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'CP';
        });
        
        $supIndex = 0;
        foreach ($cpEmployees as $cp) {
            // Assigner 3 superviseurs à chaque CP
            for ($i = 0; $i < 3; $i++) {
                if ($supIndex < $supEmployees->count()) {
                    $sup = $supEmployees->get($supIndex);
                    
                    if ($sup) {
                    // Le superviseur est rattaché directement au CP (pas à une campagne)
                    Assignment::create([
                        'employee_id' => $sup->id,
                        'campaign_id' => null, // Pas de campagne directe pour les superviseurs
                        'position_id' => $positions->where('code', 'SUP')->first()->id,
                        'manager_id' => $cp->id, // Référence à l'employee_id du CP
                        'status' => 'active',
                        'start_date' => now()->subDays(rand(15, 90))->format('Y-m-d'),
                        'end_date' => null,
                    ]);
                }
                    
                    $supIndex++;
                }
            }
        }

        // Affectations des Téléconseillers (TC) aux SUPs - tous les TC doivent avoir un superviseur
        $tcEmployees = $employees->filter(function($emp) {
            return $emp->position->code === 'TC';
        });
        
        // Récupérer tous les superviseurs actifs
        $activeSupervisors = $supEmployees->filter(function($sup) {
            return $sup->assignments->where('status', 'active')->isNotEmpty();
        });
        
        foreach ($tcEmployees as $index => $tc) {
            // Assigner chaque TC à un superviseur existant (pas à une campagne)
            if ($supEmployees->isNotEmpty()) {
                $supManager = $supEmployees->get($index % $supEmployees->count());
                
                if ($supManager) {
                    // Le TC est rattaché directement au superviseur (pas à une campagne)
                    Assignment::create([
                        'employee_id' => $tc->id,
                        'campaign_id' => null, // Pas de campagne directe pour les TC
                        'position_id' => $positions->where('code', 'TC')->first()->id,
                        'manager_id' => $supManager->id, // Référence à l'employee_id du SUP
                        'status' => 'active',
                        'start_date' => now()->subDays(rand(7, 30))->format('Y-m-d'),
                        'end_date' => null,
                    ]);
                }
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