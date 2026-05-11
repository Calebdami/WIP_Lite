<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Assignment;
use App\Models\PlanningModel;
use App\Models\PlanningAssignment;
use App\Models\PlanningHistorys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlanningAssignmentController extends Controller
{
    /**
     * Affiche l'écran d'affectation de planning pour les Superviseurs (SUP).
     * Gère le filtrage des superviseurs gérables en fonction du rôle de l'utilisateur connecté (Admin vs CP).
     */
    public function create()
    {
        $user = Auth::user();
        $isCp = $user->role->name === 'cp';
        $cpEmployeeId = $isCp ? Employee::where('user_id', $user->id)->first()?->id : null;

        // Requête de base : récupérer les employés avec la fonction SUP et une campagne active
        $supervisorsQuery = Employee::whereHas('position', function($q) {
            $q->where('code', 'SUP');
        })->whereHas('assignments', function($q) {
            $q->where('status', 'active')->whereNotNull('campaign_id');
        })->whereExists(function ($query) {
            // Un superviseur doit avoir au moins un TC actif dans son équipe pour être éligible à un planning
            $query->select(DB::raw(1))
                ->from('assignments')
                ->whereColumn('assignments.manager_id', 'employees.id')
                ->where('assignments.status', 'active');
        });

        // Si c'est un CP, il ne voit que les superviseurs sous sa responsabilité directe
        if ($isCp && $cpEmployeeId) {
            $supervisorsQuery->whereHas('assignments', function($q) use ($cpEmployeeId) {
                $q->where('manager_id', $cpEmployeeId)->where('status', 'active');
            });
        }

        $supervisors = $supervisorsQuery->with(['assignments' => function($q) {
            $q->where('status', 'active')->with('campaign');
        }])->get()->map(function($emp) {
            $activeAssignment = $emp->assignments->first();
            $teamCount = Assignment::where('manager_id', $emp->id)->where('status', 'active')->count();

            // Vérifie si le superviseur possède déjà un planning validé et en cours
            $hasActivePlanning = PlanningAssignment::where('employee_id', $emp->id)
                ->where('status', 'validé')
                ->where(function($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', today());
                })->exists();

            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'campaign' => $activeAssignment?->campaign?->name ?? 'Aucune campagne',
                'team_count' => $teamCount,
                'has_active_planning' => $hasActivePlanning
            ];
        });

        $models = PlanningModel::orderBy('name', 'asc')->get();
        $campaigns = $supervisors->pluck('campaign')->unique()->values()->all();

        $view = $isCp ? 'Cp/Schedules/AssignSup' : 'Admin/Assignments/AssignSchedule';

        return Inertia::render($view, [
            'supervisors' => $supervisors,
            'models' => $models,
            'campaigns' => $campaigns
        ]);
    }

    /**
     * Affiche l'écran d'affectation massive pour les Téléconseillers (TC).
     * Réservé aux CP (Chef de Plateau) pour gérer les équipes de leurs superviseurs.
     */
    public function assignTC()
    {
        $user = Auth::user();
        $isCp = $user->role->name === 'cp';
        $cpEmployeeId = $isCp ? Employee::where('user_id', $user->id)->first()?->id : null;

        $tcsQuery = Employee::whereHas('position', function($q) {
            $q->where('code', 'TC');
        })->whereHas('assignments', function($q) {
            // Le TC doit avoir un superviseur assigné et une campagne active
            $q->where('status', 'active')
              ->whereNotNull('manager_id')
              ->whereNotNull('campaign_id');
        });

        if ($isCp && $cpEmployeeId) {
            // Un CP ne voit que les TC gérés par les Superviseurs qui sont eux-mêmes gérés par ce CP
            $managedSupIds = Assignment::where('manager_id', $cpEmployeeId)
                ->where('status', 'active')
                ->pluck('employee_id');

            $tcsQuery->whereHas('assignments', function($q) use ($managedSupIds) {
                $q->whereIn('manager_id', $managedSupIds)->where('status', 'active');
            });
        }

        $tcs = $tcsQuery->with(['assignments' => function($q) {
            $q->where('status', 'active')->with(['campaign', 'manager']);
        }])->get()->map(function($emp) {
            $activeAssignment = $emp->assignments->first();
            
            $hasActivePlanning = PlanningAssignment::where('employee_id', $emp->id)
                ->where('status', 'validé')
                ->where(function($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', today());
                })->exists();

            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'campaign' => $activeAssignment?->campaign?->name ?? 'Aucune campagne',
                'supervisor' => $activeAssignment?->manager ? ($activeAssignment->manager->first_name . ' ' . $activeAssignment->manager->last_name) : 'Aucun',
                'has_active_planning' => $hasActivePlanning
            ];
        });

        $models = PlanningModel::orderBy('name', 'asc')->get();
        $campaigns = $tcs->pluck('campaign')->unique()->values()->all();
        $supervisorsList = $tcs->pluck('supervisor')->unique()->values()->all();

        return Inertia::render('Cp/Schedules/AssignTc', [
            'teleconsultants' => $tcs,
            'models' => $models,
            'campaigns' => $campaigns,
            'supervisorsList' => $supervisorsList
        ]);
    }

    /**
     * Enregistre une ou plusieurs affectations de planning.
     * Utilise une transaction pour garantir que l'historique est créé pour chaque affectation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'planning_model_id' => 'required|exists:planning_models,id',
            'employee_id' => 'nullable|exists:employees,id', // Pour affectation simple
            'employee_ids' => 'nullable|array',              // Pour affectation massive
            'employee_ids.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $employeeIds = $request->employee_ids ?? [$request->employee_id];

        DB::transaction(function () use ($employeeIds, $validated) {
            foreach ($employeeIds as $id) {
                if (!$id) { continue; }
                
                $assignment = PlanningAssignment::create([
                    'planning_model_id' => $validated['planning_model_id'],
                    'employee_id' => $id,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? null,
                    'status' => 'en attente'
                ]);

                // Journalisation de l'action dans l'historique de planning
                PlanningHistorys::create([
                    'planning_assignment_id' => $assignment->id,
                    'old_status' => null,
                    'new_status' => 'en attente',
                    'changed_by' => Auth::id(),
                    'reason' => 'Création de l\'affectation',
                    'created_at' => now()
                ]);
            }
        });
        
        $route = Auth::user()->role->name === 'cp' ? 'cp.schedules.templates' : 'admin.assignments.schedules';
        return redirect()->route($route)->with('success', 'Planning(s) affecté(s) avec succès.');
    }

    /**
     * Affiche l'écran de validation des plannings en attente.
     * Filtre les données en fonction du périmètre de l'utilisateur (Admin = Tout, CP = Ses équipes).
     */
    public function validationIndex(Request $request)
    {
        $user = Auth::user();
        $isCp = $user->role->name === 'cp';
        $cpEmployeeId = $isCp ? Employee::where('user_id', $user->id)->first()?->id : null;

        $query = PlanningAssignment::with(['employee.position', 'planningModel', 'validator']);

        if ($isCp && $cpEmployeeId) {
            // Un CP ne peut valider que pour son propre périmètre (ses SUPs et les TCs rattachés à ses SUPs)
            $managedSupIds = Assignment::where('manager_id', $cpEmployeeId)
                ->where('status', 'active')
                ->pluck('employee_id');

            $managedTcIds = Assignment::whereIn('manager_id', $managedSupIds)
                ->where('status', 'active')
                ->pluck('employee_id');

            $allManagedIds = $managedSupIds->merge($managedTcIds)->unique();

            $query->whereIn('employee_id', $allManagedIds);
        }

        // Filtre de recherche par nom d'employé ou nom du modèle
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('employee', function($eq) use ($search) {
                    $eq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('planningModel', function($mq) use ($search) {
                    $mq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filtre par statut (en attente, validé, suspendu)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Calcul des statistiques pour les cartes du tableau de bord
        $statsQuery = PlanningAssignment::query();
        if ($isCp && isset($allManagedIds)) {
            $statsQuery->whereIn('employee_id', $allManagedIds);
        }

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'en attente')->count(),
            'validated' => (clone $statsQuery)->where('status', 'validé')->count(),
            'suspended' => (clone $statsQuery)->where('status', 'suspendu')->count(),
        ];

        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/Validation' : 'Admin/Assignments/Validation';

        return Inertia::render($view, [
            'assignments' => $assignments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Met à jour le statut d'une affectation individuelle (Valider/Refuser/Suspendre).
     */
    public function updateStatus(Request $request, PlanningAssignment $assignment)
    {
        $request->validate([
            'status' => 'required|in:en attente,validé,suspendu',
            'reason' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $assignment) {
            $this->processStatusUpdate($assignment, $request->status, $request->reason);
        });

        return back()->with('success', 'Statut mis à jour avec succès');
    }

    /**
     * Met à jour massivement le statut de plusieurs affectations sélectionnées.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:planning_assignments,id',
            'status' => 'required|in:en attente,validé,suspendu',
            'reason' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $id) {
                $assignment = PlanningAssignment::findOrFail($id);
                $this->processStatusUpdate($assignment, $request->status, $request->reason ?? 'Mise à jour groupée');
            }
        });

        return back()->with('success', 'Statuts mis à jour avec succès');
    }

    /**
     * Logique interne de mise à jour du statut et de création d'historique.
     * Gère les métadonnées de validation (qui a validé et quand).
     */
    private function processStatusUpdate($assignment, $status, $reason)
    {
        $oldStatus = $assignment->status;

        $updateData = [
            'status' => $status,
        ];

        // Si le statut passe à 'validé', on enregistre l'utilisateur et le timestamp actuel
        if ($status === 'validé') {
            $updateData['validated_by'] = Auth::id();
            $updateData['validated_at'] = now();
            $reason = $reason ?? 'Validation du planning';
        } 
        // Si suspendu, on retire les métadonnées de validation
        elseif ($status === 'suspendu') {
            $updateData['validated_by'] = null;
            $updateData['validated_at'] = null;
            $reason = $reason ?? 'Suspension du planning';
        } elseif ($status === 'en attente' && $oldStatus === 'suspendu') {
            $reason = $reason ?? 'Réactivation du planning';
        }

        $assignment->update($updateData);

        // Enregistrement obligatoire dans l'historique pour l'audit
        PlanningHistorys::create([
            'planning_assignment_id' => $assignment->id,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'changed_by' => Auth::id(),
            'reason' => $reason ?? 'Mise à jour du statut',
            'created_at' => now()
        ]);
    }
}
