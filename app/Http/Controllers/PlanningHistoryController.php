<?php

namespace App\Http\Controllers;

use App\Models\PlanningHistorys;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningHistoryController extends Controller
{
    /**
     * Affiche l'historique complet des actions sur les plannings (Audit Trail).
     * Gère la recherche, le filtrage par périmètre de rôle et les statistiques d'activité.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isCp = $user->role->name === 'cp';
        $cpEmployeeId = $isCp ? \App\Models\Employee::where('user_id', $user->id)->first()?->id : null;

        // Requête de base avec chargement des relations nécessaires (affectation, employé, auteur de la modif)
        $query = PlanningHistorys::with(['assignment.employee', 'assignment.planningModel', 'author']);

        if ($isCp && $cpEmployeeId) {
            // Filtrage : Le CP ne voit que l'historique concernant ses équipes (SUP et TC)
            $managedSupIds = \App\Models\Assignment::where('manager_id', $cpEmployeeId)
                ->where('status', 'active')
                ->pluck('employee_id');

            $managedTcIds = \App\Models\Assignment::whereIn('manager_id', $managedSupIds)
                ->where('status', 'active')
                ->pluck('employee_id');

            $allManagedIds = $managedSupIds->merge($managedTcIds)->unique();

            $query->whereHas('assignment', function($q) use ($allManagedIds) {
                $q->whereIn('employee_id', $allManagedIds);
            });
        }

        // Filtre de recherche par nom d'employé ou par motif (raison du changement)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('assignment.employee', function($eq) use ($search) {
                    $eq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Nouveau filtre par statut
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('new_status', $request->status);
        }

        // Nouveau filtre par modèle de planning
        if ($request->filled('model_id') && $request->model_id !== 'all') {
            $query->whereHas('assignment', function($q) use ($request) {
                $q->where('planning_model_id', $request->model_id);
            });
        }

        $history = $query->latest()->paginate(15)->withQueryString();

        // Récupération de tous les modèles pour le dropdown du filtre
        $planningModels = \App\Models\PlanningModel::orderBy('name')->get(['id', 'name']);

        // Calcul des statistiques d'activité pour le tableau de bord d'historique
        $statsQuery = PlanningHistorys::query();
        if ($isCp && isset($allManagedIds)) {
            $statsQuery->whereHas('assignment', function($q) use ($allManagedIds) {
                $q->whereIn('employee_id', $allManagedIds);
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'validations' => (clone $statsQuery)->where('new_status', 'validé')->count(),
            'creations' => (clone $statsQuery)->whereNull('old_status')->count(), // old_status null = création initiale
            'suspensions' => (clone $statsQuery)->where('new_status', 'suspendu')->count(),
        ];

        // Redirection vers la vue correspondante (Admin ou CP)
        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/History' : 'Admin/Assignments/History';

        return Inertia::render($view, [
            'history' => $history,
            'stats' => $stats,
            'planningModels' => $planningModels,
            'filters' => $request->only(['search', 'status', 'model_id'])
        ]);
    }

    /**
     * API : Récupère la chronologie complète des changements pour une affectation spécifique.
     * Utilisé pour afficher l'historique détaillé dans une modal ou un tiroir (drawer).
     */
    public function showByAssignment($assignmentId)
    {
        $history = PlanningHistorys::where('planning_assignment_id', $assignmentId)
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }
}