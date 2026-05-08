<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function structure(Request $request)
    {
        $search = $request->input('search');

        // 1. Campagnes actives filtrées par recherche
        $campaignsQuery = Campaign::where('status', 'active');
        if ($search) {
            $campaignsQuery->where('name', 'like', "%{$search}%");
        }
        $campaigns = $campaignsQuery->paginate(3)->withQueryString(); 

        // 2. Affectations pour l'arbre hiérarchique
        $campaignIds = $campaigns->pluck('id');
        $activeAssignments = Assignment::where('status', 'active')
            ->whereIn('campaign_id', $campaignIds)
            ->with(['employee.position', 'manager.position', 'campaign', 'position'])
            ->get();

        // 3. Toutes les campagnes actives pour la modale
        $allCampaigns = Campaign::where('status', 'active')->get();

        // 4. Tous les managers pour la modale
        $allManagers = Assignment::where('status', 'active')
            ->whereHas('position', function($q) {
                $q->whereIn('code', ['CP', 'SUP']);
            })
            ->with(['employee', 'campaign', 'position'])
            ->get();

        // 5. Ressources disponibles (Non affectées OU CPs car multi-campagnes)
        $availableQuery = Employee::query()->with('position');
        
        if ($search) {
            $availableQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        // On affiche les ressources qui n'ont pas d'affectation ACTIVE
        // SAUF les CP qui peuvent avoir plusieurs campagnes
        // Et on filtre par statut employé (actif ou inactif uniquement)
        $availableEmployees = $availableQuery->whereIn('status', ['actif', 'inactif'])
            ->where(function($q) {
                $q->whereDoesntHave('assignments', function($sq) {
                    $sq->where('status', 'active');
                })->orWhereHas('position', function($sq) {
                    $sq->where('code', 'CP');
                });
            })->paginate(12, ['*'], 'available_page')->withQueryString();

        return Inertia::render('Admin/Assignments/Structure', [
            'campaigns' => $campaigns,
            'assignments' => $activeAssignments,
            'allCampaigns' => $allCampaigns,
            'allManagers' => $allManagers,
            'availableEmployees' => $availableEmployees,
            'positions' => Position::all(),
            'filters' => $request->only(['search']),
            'assign' => $request->input('assign'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable', // Sera traité manuellement pour extraire l'ID employé
            'start_date' => 'required|date',
        ]);

        $employee = Employee::find($request->employee_id);
        $position = Position::find($request->position_id);

        // Si un manager_id est fourni, c'est l'ID de son AFFECTATION (Assignment ID)
        $managerEmployeeId = null;
        if ($request->manager_id) {
            $managerAssignment = Assignment::find($request->manager_id);
            if ($managerAssignment) {
                $managerEmployeeId = $managerAssignment->employee_id;
            }
        }

        // Règle CP : peut être affecté à plusieurs campagnes mais pas la même deux fois
        if ($position->code === 'CP') {
            $existing = Assignment::where('employee_id', $request->employee_id)
                ->where('campaign_id', $request->campaign_id)
                ->where('status', 'active')
                ->exists();
            
            if ($existing) {
                return back()->withErrors(['error' => 'Ce Chef de Plateau est déjà affecté à cette campagne.']);
            }
        } else {
            // Autres rôles : un seul rôle actif à la fois
            $hasActive = Assignment::where('employee_id', $request->employee_id)
                ->where('status', 'active')
                ->exists();
            
            if ($hasActive) {
                return back()->withErrors(['error' => 'Cette ressource est déjà affectée à une campagne.']);
            }
        }

        $newAssignment = Assignment::create([
            'employee_id' => $validated['employee_id'],
            'campaign_id' => $validated['campaign_id'],
            'position_id' => $validated['position_id'],
            'manager_id' => $managerEmployeeId,
            'start_date' => $validated['start_date'],
            'status' => 'active',
        ]);

        // --- Auto-rattachement des collaborateurs orphelins ---
        // Si on affecte un CP → rattacher les SUPs orphelins de cette campagne
        if ($position->code === 'CP') {
            $supPositionId = Position::where('code', 'SUP')->value('id');
            if ($supPositionId) {
                Assignment::where('campaign_id', $validated['campaign_id'])
                    ->where('position_id', $supPositionId)
                    ->where('status', 'active')
                    ->whereNull('manager_id')
                    ->update(['manager_id' => $newAssignment->employee_id]);
            }
        }

        // Si on affecte un SUP → rattacher les TCs orphelins de cette campagne
        if ($position->code === 'SUP') {
            $tcPositionId = Position::where('code', 'TC')->value('id');
            if ($tcPositionId) {
                Assignment::where('campaign_id', $validated['campaign_id'])
                    ->where('position_id', $tcPositionId)
                    ->where('status', 'active')
                    ->whereNull('manager_id')
                    ->update(['manager_id' => $newAssignment->employee_id]);
            }
        }

        return redirect()->back()->with('success', 'Affectation réussie.');
    }

    public function release(Request $request, Assignment $assignment)
    {
        $cascade = $request->input('cascade', true);

        $assignment->update([
            'status' => 'terminated',
            'end_date' => now(),
        ]);

        if ($cascade) {
            // Libération en cascade : si on libère un manager, ses subordonnés directs sont aussi libérés
            $subordinates = Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'active')
                ->get();

            foreach ($subordinates as $sub) {
                // On appelle la libération récursivement avec cascade
                $sub->update([
                    'status' => 'terminated',
                    'end_date' => now(),
                ]);
                
                // On continue la cascade pour les subordonnés du subordonné
                $this->recursiveRelease($sub->employee_id, $sub->campaign_id);
            }
        } else {
            // Si pas de cascade, on met simplement à jour les subordonnés pour qu'ils n'aient plus de manager_id
            Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'active')
                ->update(['manager_id' => null]);
        }

        return redirect()->back()->with('success', 'Ressource libérée avec succès.');
    }

    protected function recursiveRelease($employeeId, $campaignId)
    {
        $subordinates = Assignment::where('manager_id', $employeeId)
            ->where('campaign_id', $campaignId)
            ->where('status', 'active')
            ->get();

        foreach ($subordinates as $sub) {
            $sub->update([
                'status' => 'terminated',
                'end_date' => now(),
            ]);
            $this->recursiveRelease($sub->employee_id, $campaignId);
        }
    }

    public function resources(Request $request)
    {
        $query = Employee::whereIn('status', ['actif', 'inactif'])
            ->with(['position', 'assignments' => function($q) {
                $q->where('status', 'active')->with('campaign');
            }])
            ->where(function($q) {
                // Exclure les employés qui ont déjà des affectations actives
                // SAUF les CPs qui peuvent avoir plusieurs campagnes
                $q->whereDoesntHave('assignments', function($subQ) {
                    $subQ->where('status', 'active');
                })->orWhereHas('position', function($posQ) {
                    $posQ->where('code', 'CP');
                });
            });

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'assigned') {
            $query->whereHas('assignments', function($q) {
                $q->where('status', 'active');
            });
        } elseif ($request->status === 'unassigned') {
            $query->whereDoesntHave('assignments', function($q) {
                $q->where('status', 'active');
            });
        }

        return Inertia::render('Admin/Assignments/Resources', [
            'employees' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Affiche la page d'affectation multiple
     */
    public function bulkAssign(Request $request)
    {
        $search = $request->input('search');
        
        // Récupérer tous les employés disponibles pour l'affectation multiple
        $employeesQuery = Employee::with('position')
            ->whereIn('status', ['actif', 'inactif']);

        if ($search) {
            $employeesQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        // Filtrer les ressources disponibles (non affectées ou CP pour multi-campagnes)
        $employees = $employeesQuery->where(function($q) {
            $q->whereDoesntHave('assignments', function($sq) {
                $sq->where('status', 'active');
            })->orWhereHas('position', function($sq) {
                $sq->where('code', 'CP');
            });
        })->paginate(15)->withQueryString();

        // Récupérer toutes les campagnes actives
        $campaigns = Campaign::where('status', 'active')->get();

        // Récupérer les managers actifs pour les affectations hiérarchiques
        $managers = Assignment::where('status', 'active')
            ->whereHas('position', function($q) {
                $q->whereIn('code', ['CP', 'SUP']);
            })
            ->with(['employee', 'campaign', 'position'])
            ->get()
            ->groupBy(function($assignment) {
                return $assignment->position->code;
            });

        return Inertia::render('Admin/Assignments/BulkAssign', [
            'employees' => $employees,
            'campaigns' => $campaigns,
            'managers' => $managers,
            'positions' => Position::all(),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Traite l'affectation multiple
     */
    public function bulkAssignStore(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'assignment_type' => 'required|in:campaign,manager',
            'campaign_ids' => 'required_if:assignment_type,campaign|array|min:1',
            'campaign_ids.*' => 'exists:campaigns,id',
            'manager_assignment_id' => 'required_if:assignment_type,manager|exists:assignments,id',
            'position_id' => 'required|exists:positions,id',
            'start_date' => 'required|date',
        ]);

        $position = Position::find($validated['position_id']);
        $successCount = 0;
        $errors = [];

        if ($validated['assignment_type'] === 'campaign') {
            // Affectation multiple à des campagnes (CP → campagnes multiples)
            foreach ($validated['employee_ids'] as $employeeId) {
                foreach ($validated['campaign_ids'] as $campaignId) {
                    try {
                        // Vérifier si l'affectation n'existe pas déjà
                        $existing = Assignment::where('employee_id', $employeeId)
                            ->where('campaign_id', $campaignId)
                            ->where('position_id', $validated['position_id'])
                            ->where('status', 'active')
                            ->first();

                        if ($existing) {
                            $errors[] = "L'employé #{$employeeId} est déjà affecté à cette campagne.";
                            continue;
                        }

                        // Pour les CP, vérifier qu'ils ne sont pas déjà affectés à cette campagne
                        if ($position->code === 'CP') {
                            $existingCP = Assignment::where('employee_id', $employeeId)
                                ->where('campaign_id', $campaignId)
                                ->where('status', 'active')
                                ->exists();
                            
                            if ($existingCP) {
                                $errors[] = "Ce Chef de Plateau est déjà affecté à la campagne #{$campaignId}.";
                                continue;
                            }
                        } else {
                            // Pour les autres rôles, vérifier qu'ils n'ont pas d'affectation active
                            $hasActive = Assignment::where('employee_id', $employeeId)
                                ->where('status', 'active')
                                ->exists();
                            
                            if ($hasActive) {
                                $errors[] = "L'employé #{$employeeId} a déjà une affectation active.";
                                continue;
                            }
                        }

                        Assignment::create([
                            'employee_id' => $employeeId,
                            'campaign_id' => $campaignId,
                            'position_id' => $validated['position_id'],
                            'start_date' => $validated['start_date'],
                            'status' => 'active',
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Erreur lors de l'affectation de l'employé #{$employeeId} à la campagne #{$campaignId}: " . $e->getMessage();
                    }
                }
            }
        } else {
            // Affectation multiple à un manager (SUP/TC → manager)
            $managerAssignment = Assignment::find($validated['manager_assignment_id']);
            
            if (!$managerAssignment) {
                return back()->withErrors(['error' => 'Manager non trouvé.']);
            }

            foreach ($validated['employee_ids'] as $employeeId) {
                try {
                    // Vérifier que l'employé n'a pas déjà d'affectation active
                    $hasActive = Assignment::where('employee_id', $employeeId)
                        ->where('status', 'active')
                        ->exists();
                    
                    if ($hasActive) {
                        $errors[] = "L'employé #{$employeeId} a déjà une affectation active.";
                        continue;
                    }

                    Assignment::create([
                        'employee_id' => $employeeId,
                        'campaign_id' => $managerAssignment->campaign_id,
                        'position_id' => $validated['position_id'],
                        'manager_id' => $managerAssignment->employee_id,
                        'start_date' => $validated['start_date'],
                        'status' => 'active',
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Erreur lors de l'affectation de l'employé #{$employeeId} au manager: " . $e->getMessage();
                }
            }
        }

        $message = "{$successCount} affectation(s) réussie(s).";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(' ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * API pour récupérer les managers disponibles selon le type de position
     */
    public function getAvailableManagers(Request $request)
    {
        $positionCode = $request->input('position_code');
        
        if (!$positionCode) {
            return response()->json([]);
        }

        $managers = Assignment::where('status', 'active')
            ->whereHas('position', function($q) use ($positionCode) {
                if ($positionCode === 'SUP') {
                    $q->where('code', 'CP');
                } elseif ($positionCode === 'TC') {
                    $q->where('code', 'SUP');
                }
            })
            ->with(['employee', 'campaign', 'position'])
            ->get();

        return response()->json($managers);
    }
}
