<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PlanningAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TcPlanningController extends Controller
{
    /**
     * Affiche le planning complet du téléconseiller connecté.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Informations du Téléconseiller (currentTelemarketer)
        $employee = Employee::where('user_id', $user->id)
            ->with(['assignments' => function($q) {
                $q->where('status', 'active')->with(['campaign', 'manager']);
            }])
            ->first();

        if (!$employee) {
            return Inertia::render('Tc/Schedule/Index', [
                'currentTelemarketer' => null,
                'message' => 'Profil employé non trouvé.'
            ]);
        }

        $activeAssignment = $employee->assignments->first();

        $currentTelemarketer = [
            'id' => $employee->id,
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'role' => $employee->position?->name ?? 'Téléconseiller',
            'supervisor' => $activeAssignment?->manager ? ($activeAssignment->manager->first_name . ' ' . $activeAssignment->manager->last_name) : 'Non assigné',
            'campaign' => $activeAssignment?->campaign?->name ?? 'Non assigné',
        ];

        // 2. Planning Actif (En attente ou Validé)
        $activePlanning = PlanningAssignment::where('employee_id', $employee->id)
            ->whereIn('status', ['validé', 'en attente'])
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->with('planningModel')
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. Historique du planning actuel
        $planningHistory = [];
        if ($activePlanning) {
            $planningHistory = DB::table('planning_histories')
                ->where('planning_assignment_id', $activePlanning->id)
                ->join('users', 'planning_histories.changed_by', '=', 'users.id')
                ->select('planning_histories.*', 'users.name as changed_by_name')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        // 4. Plannings précédents
        $pastPlannings = PlanningAssignment::where('employee_id', $employee->id)
            ->where('status', 'terminé') // On suppose un statut 'terminé' pour l'historique
            ->with('planningModel')
            ->orderBy('end_date', 'desc')
            ->get();

        return Inertia::render('Tc/Schedule/Index', [
            'currentTelemarketer' => $currentTelemarketer,
            'activePlanning' => $activePlanning,
            'planningModel' => $activePlanning?->planningModel,
            'planningHistory' => $planningHistory,
            'pastPlannings' => $pastPlannings,
            'employee' => $currentTelemarketer, // Alias pour compatibilité si besoin
        ]);
    }
}
