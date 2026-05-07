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

        Assignment::create([
            'employee_id' => $validated['employee_id'],
            'campaign_id' => $validated['campaign_id'],
            'position_id' => $validated['position_id'],
            'manager_id' => $managerEmployeeId,
            'start_date' => $validated['start_date'],
            'status' => 'active',
        ]);

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
            }]);

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
            'employees' => $query->paginate(8)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
