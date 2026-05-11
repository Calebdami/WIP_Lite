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

        // Aucune affectation pour les Superviseurs (SUP) - ils doivent être disponibles
        // Les superviseurs ne sont pas affectés automatiquement dans le seeder
        // Ils seront disponibles pour l'affectation multiple

        // Aucune affectation pour les Téléconseillers (TC) - ils doivent être disponibles
        // Les téléconseillers ne sont pas affectés automatiquement dans le seeder
        // Ils seront disponibles pour l'affectation multiple
    }
}