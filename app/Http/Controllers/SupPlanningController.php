<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\PlanningAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupPlanningController extends Controller
{
    /**
     * Affiche le tableau de bord des plannings pour le Superviseur.
     * Cette vue console :
     * 1. Le planning personnel du superviseur.
     * 2. Les plannings des téléconseillers (TC) sous sa responsabilité directe.
     * 3. Des statistiques globales pour l'équipe.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Identification et informations de base du Superviseur
        $supEmployee = Employee::where('user_id', $user->id)
            ->with(['assignments' => function($q) {
                // On récupère uniquement la campagne active du superviseur
                $q->where('status', 'active')->with('campaign');
            }])
            ->first();

        if (!$supEmployee) {
            return Inertia::render('Sup/Schedule/Index', [
                'currentSupervisor' => null,
                'message' => 'Profil superviseur non trouvé.'
            ]);
        }

        $activeAssignment = $supEmployee->assignments->first();

        $currentSupervisor = [
            'id' => $supEmployee->id,
            'name' => $supEmployee->first_name . ' ' . $supEmployee->last_name,
            'role' => 'Superviseur',
            'campaign' => $activeAssignment?->campaign?->name ?? 'Non assigné',
        ];

        // 2. Récupération du planning personnel du Superviseur (Mon planning)
        // On cherche le planning le plus pertinent (Validé > En attente > Suspendu) pour la période actuelle
        $myPlanning = PlanningAssignment::where('employee_id', $supEmployee->id)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->with('planningModel')
            ->get()
            ->sortByDesc('created_at') // On prend le plus récent par défaut
            ->sortBy(function($pa) {
                // Mais on priorise 'validé' puis 'en attente' par rapport à 'suspendu'
                return match($pa->status) {
                    'validé' => 1,
                    'en attente' => 2,
                    'suspendu' => 3,
                    default => 4
                };
            })
            ->first();

        // 3. Récupération des plannings de l'équipe (Dernier planning de chaque TC)
        // Étape A : Identifier les membres de l'équipe active
        $teamEmployeeIds = Assignment::where('manager_id', $supEmployee->id)
            ->where('status', 'active')
            ->pluck('employee_id');

        // Étape B : Récupérer le dernier planning pour chaque membre
        $teamPlannings = PlanningAssignment::whereIn('employee_id', $teamEmployeeIds)
            ->with(['planningModel', 'employee'])
            ->get()
            ->groupBy('employee_id')
            ->map(function ($group) {
                // Pour chaque employé, on prend le planning le plus récent MAIS on priorise le statut 'validé'
                return $group->sortByDesc('created_at')
                    ->sortBy(function($pa) {
                        return match($pa->status) {
                            'validé' => 1,
                            'en attente' => 2,
                            'suspendu' => 3,
                            default => 4
                        };
                    })
                    ->first();
            })
            ->values() // Revenir à une collection indexée numériquement
            ->map(function($pa) {
                return [
                    'id' => $pa->id,
                    'tc_name' => $pa->employee->first_name . ' ' . $pa->employee->last_name,
                    'start_date' => $pa->start_date,
                    'end_date' => $pa->end_date,
                    'status' => $pa->status,
                    'planning_model' => $pa->planningModel,
                    'total_hours' => $pa->planningModel?->total_hours ?? 0,
                ];
            });

        // 4. Consolidation des statistiques pour l'en-tête du dashboard
        $stats = [
            'total_people' => count($teamEmployeeIds) + 1, // L'équipe entière + le SUP
            'validated_count' => $teamPlannings->where('status', 'validé')->count() + ($myPlanning?->status === 'validé' ? 1 : 0),
            'pending_count' => $teamPlannings->where('status', 'en attente')->count() + ($myPlanning?->status === 'en attente' ? 1 : 0),
            'total_hours' => $teamPlannings->sum('total_hours') + ($myPlanning?->planningModel?->total_hours ?? 0),
        ];

        return Inertia::render('Sup/Schedule/Index', [
            'currentSupervisor' => $currentSupervisor,
            'myPlanning' => $myPlanning,
            'teamPlannings' => $teamPlannings,
            'stats' => $stats,
        ]);
    }
}
