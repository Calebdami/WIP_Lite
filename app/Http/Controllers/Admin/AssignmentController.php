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
        // 1. Campagnes actives paginées
        $campaigns = Campaign::where('status', 'active')->paginate(2); // On pagine par 2 campagnes pour la vue structure car c'est dense

        // 2. Toutes les affectations actives pour construire la structure dans Vue
        $activeAssignments = Assignment::where('status', 'active')
            ->with(['employee.position', 'manager.position', 'campaign', 'position'])
            ->get();

        // 3. Ressources non affectées
        $availableEmployees = Employee::whereDoesntHave('assignments', function($q) {
            $q->where('status', 'active');
        })->with('position')->get();

        return Inertia::render('Admin/Assignments/Structure', [
            'campaigns' => $campaigns,
            'assignments' => $activeAssignments,
            'availableEmployees' => $availableEmployees,
            'positions' => Position::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'manager_id' => 'nullable|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'start_date' => 'required|date',
        ]);

        // Vérification si déjà affecté (sécurité supplémentaire)
        $existing = Assignment::where('employee_id', $validated['employee_id'])
            ->where('status', 'active')
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', 'Cet employé est déjà affecté à une campagne.');
        }

        Assignment::create($validated);

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
        $query = Employee::with(['position', 'assignments' => function($q) {
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
