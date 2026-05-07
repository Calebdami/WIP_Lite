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
     * Affiche le tableau de bord personnel du Téléconseiller (TC).
     * Permet au TC de visualiser :
     * 1. Son planning actuel (validé ou en attente).
     * 2. Le détail des heures de son modèle de planning.
     * 3. L'historique des validations/changements sur son planning actuel.
     * 4. Ses informations de rattachement (Superviseur, Campagne).
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Identification et informations de base du Téléconseiller
        $employee = Employee::where('user_id', $user->id)
            ->with(['assignments' => function($q) {
                // On récupère la campagne active et le superviseur direct
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

        // 2. Récupération du planning actif (celui qui régit la semaine actuelle)
        $activePlanning = PlanningAssignment::where('employee_id', $employee->id)
            ->whereIn('status', ['validé', 'en attente'])
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->with('planningModel')
            ->orderBy('created_at', 'desc')
            ->first();

        // 3. Extraction de la chronologie des évènements pour le planning actuel
        // Permet au TC de voir qui a validé son planning et quand.
        $planningHistory = [];
        if ($activePlanning) {
            $planningHistory = DB::table('planning_histories')
                ->where('planning_assignment_id', $activePlanning->id)
                ->join('users', 'planning_histories.changed_by', '=', 'users.id')
                ->select('planning_histories.*', 'users.name as changed_by_name')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        // 4. Archive des anciens plannings (ceux qui sont terminés)
        $pastPlannings = PlanningAssignment::where('employee_id', $employee->id)
            ->where('status', 'terminé')
            ->with('planningModel')
            ->orderBy('end_date', 'desc')
            ->get();

        return Inertia::render('Tc/Schedule/Index', [
            'currentTelemarketer' => $currentTelemarketer,
            'activePlanning' => $activePlanning,
            'planningModel' => $activePlanning?->planningModel,
            'planningHistory' => $planningHistory,
            'pastPlannings' => $pastPlannings,
            'employee' => $currentTelemarketer, // Alias pour compatibilité
        ]);
    }
}
