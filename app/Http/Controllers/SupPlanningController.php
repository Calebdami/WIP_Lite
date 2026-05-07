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
     * Affiche le planning du superviseur et de son équipe.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Informations du Superviseur (currentSupervisor)
        $supEmployee = Employee::where('user_id', $user->id)
            ->with(['assignments' => function($q) {
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

        // 2. Mon planning personnel (myPlanning)
        $myPlanning = PlanningAssignment::where('employee_id', $supEmployee->id)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->with('planningModel')
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. Plannings de mon équipe (teamPlannings)
        // Trouver les IDs des TC gérés par ce superviseur
        $teamEmployeeIds = Assignment::where('manager_id', $supEmployee->id)
            ->where('status', 'active')
            ->pluck('employee_id');

        $teamPlannings = PlanningAssignment::whereIn('employee_id', $teamEmployeeIds)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->with(['planningModel', 'employee'])
            ->get()
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

        // 4. Statistiques
        $stats = [
            'total_people' => count($teamEmployeeIds) + 1, // TCs + le SUP lui-même
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
