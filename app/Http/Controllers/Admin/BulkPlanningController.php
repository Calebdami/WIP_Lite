<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PlanningAssignment;
use App\Models\PlanningModel;
use App\Models\Assignment;
use App\Models\PlanningHistorys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BulkPlanningController extends Controller
{
    /**
     * Affiche la page d'affectation multiple de plannings
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // 1. Récupérer tous les employés avec leurs affectations actives
        $employeesQuery = Employee::with([
            'position', 
            'assignments' => function($q) {
                $q->where('status', 'active')->with('campaign', 'manager.employee');
            },
            'planningAssignments' => function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->startOfDay());
                })->with('planningModel');
            }
        ])->whereIn('status', ['actif', 'inactif']);

        // Filtrer par recherche
        if ($search) {
            $employeesQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        // Exclure l'admin
        $employeesQuery->whereDoesntHave('user', function($q) {
            $q->where('email', 'admin@example.com');
        });

        $employees = $employeesQuery->paginate(50)->withQueryString();

        // 2. Récupérer tous les modèles de planning disponibles
        $planningModels = PlanningModel::with('creator')->orderBy('name')->get();

        // 3. Statistiques pour le dashboard
        $stats = [
            'total_employees' => $employees->total(),
            'employees_with_planning' => PlanningAssignment::where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })->distinct('employee_id')->count(),
            'pending_validations' => PlanningAssignment::where('status', 'en attente')->count(),
            'total_planning_models' => $planningModels->count(),
        ];

        return Inertia::render('Admin/Assignments/BulkPlanning', [
            'employees' => $employees,
            'planningModels' => $planningModels,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Traite l'affectation multiple de plannings
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'planning_model_id' => 'required|exists:planning_models,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'action' => 'required|in:assign,replace,extend',
        ]);

        $planningModel = PlanningModel::find($validated['planning_model_id']);
        $successCount = 0;
        $errors = [];
        $currentUser = Auth::user();

        foreach ($validated['employee_ids'] as $employeeId) {
            try {
                $employee = Employee::find($employeeId);
                
                // Vérifier si l'employé existe et a une affectation active
                $activeAssignment = Assignment::where('employee_id', $employeeId)
                    ->where('status', 'active')
                    ->first();

                if (!$activeAssignment) {
                    $errors[] = "L'employé {$employee->first_name} {$employee->last_name} n'a pas d'affectation active.";
                    continue;
                }

                // Gérer selon l'action demandée
                switch ($validated['action']) {
                    case 'assign':
                        // Nouvelle affectation (vérifier qu'il n'y en a pas déjà une active)
                        $existingPlanning = PlanningAssignment::where('employee_id', $employeeId)
                            ->where(function($q) {
                                $q->whereNull('end_date')
                                  ->orWhere('end_date', '>=', now()->startOfDay());
                            })
                            ->first();

                        if ($existingPlanning) {
                            $errors[] = "L'employé {$employee->first_name} {$employee->last_name} a déjà un planning actif.";
                            continue 2;
                        }

                        $this->createPlanningAssignment($employeeId, $validated, $currentUser->id);
                        $successCount++;
                        break;

                    case 'replace':
                        // Remplacer le planning existant
                        $this->terminateExistingPlanning($employeeId, $currentUser->id, 'Remplacement par planning multiple');
                        $this->createPlanningAssignment($employeeId, $validated, $currentUser->id);
                        $successCount++;
                        break;

                    case 'extend':
                        // Étendre ou modifier le planning existant
                        $existingPlanning = PlanningAssignment::where('employee_id', $employeeId)
                            ->where(function($q) {
                                $q->whereNull('end_date')
                                  ->orWhere('end_date', '>=', now()->startOfDay());
                            })
                            ->first();

                        if (!$existingPlanning) {
                            $errors[] = "L'employé {$employee->first_name} {$employee->last_name} n'a pas de planning à étendre.";
                            continue 2;
                        }

                        // Mettre à jour le planning existant
                        $existingPlanning->update([
                            'planning_model_id' => $validated['planning_model_id'],
                            'start_date' => $validated['start_date'],
                            'end_date' => $validated['end_date'] ?? null,
                        ]);

                        // Créer l'historique
                        PlanningHistorys::create([
                            'planning_assignment_id' => $existingPlanning->id,
                            'old_status' => $existingPlanning->status,
                            'new_status' => $existingPlanning->status,
                            'changed_by' => $currentUser->id,
                            'reason' => 'Modification par planning multiple',
                            'created_at' => now(),
                        ]);

                        $successCount++;
                        break;
                }

            } catch (\Exception $e) {
                $errors[] = "Erreur lors du traitement de l'employé #{$employeeId}: " . $e->getMessage();
            }
        }

        $message = "{$successCount} planning(s) affecté(s) avec succès.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(' ', array_slice($errors, 0, 3)); // Limiter les erreurs affichées
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Crée une nouvelle affectation de planning
     */
    private function createPlanningAssignment($employeeId, $validated, $userId)
    {
        $planningAssignment = PlanningAssignment::create([
            'planning_model_id' => $validated['planning_model_id'],
            'employee_id' => $employeeId,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'en attente',
            'validated_by' => null,
            'validated_at' => null,
        ]);

        // Créer l'historique
        PlanningHistorys::create([
            'planning_assignment_id' => $planningAssignment->id,
            'old_status' => null,
            'new_status' => 'en attente',
            'changed_by' => $userId,
            'reason' => 'Création par planning multiple',
            'created_at' => now(),
        ]);

        return $planningAssignment;
    }

    /**
     * Termine le planning existant d'un employé
     */
    private function terminateExistingPlanning($employeeId, $userId, $reason)
    {
        $existingPlanning = PlanningAssignment::where('employee_id', $employeeId)
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->startOfDay());
            })
            ->first();

        if ($existingPlanning) {
            $oldStatus = $existingPlanning->status;
            
            $existingPlanning->update([
                'end_date' => now()->subDay(), // Terminer hier
                'status' => 'clôturé',
            ]);

            // Créer l'historique
            PlanningHistorys::create([
                'planning_assignment_id' => $existingPlanning->id,
                'old_status' => $oldStatus,
                'new_status' => 'clôturé',
                'changed_by' => $userId,
                'reason' => $reason,
                'created_at' => now(),
            ]);
        }

        return $existingPlanning;
    }

    /**
     * API pour récupérer les détails d'un employé
     */
    public function getEmployeeDetails(Request $request)
    {
        $employeeId = $request->input('employee_id');
        
        if (!$employeeId) {
            return response()->json(['error' => 'Employee ID required'], 400);
        }

        $employee = Employee::with([
            'position',
            'assignments' => function($q) {
                $q->where('status', 'active')->with('campaign', 'manager.employee');
            },
            'planningAssignments' => function($q) {
                $q->where(function($subQ) {
                    $subQ->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->startOfDay());
                })->with('planningModel');
            }
        ])->find($employeeId);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    /**
     * Supprime une affectation de planning
     */
    public function destroy(Request $request, PlanningAssignment $planningAssignment)
    {
        $currentUser = Auth::user();
        
        // Créer l'historique avant suppression
        PlanningHistorys::create([
            'planning_assignment_id' => $planningAssignment->id,
            'old_status' => $planningAssignment->status,
            'new_status' => 'supprimé',
            'changed_by' => $currentUser->id,
            'reason' => 'Suppression manuelle',
            'created_at' => now(),
        ]);

        $planningAssignment->delete();

        return redirect()->back()->with('success', 'Planning supprimé avec succès.');
    }
}
