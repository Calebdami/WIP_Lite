<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\EmployeeEvaluation;
use App\Models\EmployeeContract;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['position', 'user.role', 'assignments.campaign']);

        // Search criteria
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->role_id) {
            $role = Role::find($request->role_id);
            if ($role) {
                $query->where(function($q) use ($request, $role) {
                    $q->whereHas('user', function($sq) use ($request) {
                        $sq->where('role_id', $request->role_id);
                    })->orWhereHas('position', function($sq) use ($role) {
                        // On mappe le nom du rôle au code du poste (ex: 'tc' -> 'TC')
                        $sq->where('code', strtoupper($role->name));
                    });
                });
            }
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->position_id) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->assignment_status) {
            if ($request->assignment_status === 'assigned') {
                $query->whereHas('assignments', function($q) {
                    $q->where('status', 'active');
                });
            } else {
                $query->whereDoesntHave('assignments', function($q) {
                    $q->where('status', 'active');
                });
            }
        }

        if ($request->campaign_id) {
            $query->whereHas('assignments', function($q) use ($request) {
                $q->where('campaign_id', $request->campaign_id)
                  ->where('status', 'active');
            });
        }

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'role_id', 'status', 'position_id', 'assignment_status', 'campaign_id']),
            'roles' => Role::all(),
            'positions' => Position::all(),
            // Campaigns could be added here if needed for filters
        ]);
    }

    /**
     * Display the employee history.
     */
    public function history(Request $request)
    {
        $search = $request->search;

        // 1. Career History (Position, Status, Salary)
        $careerQuery = EmployeeHistory::with(['employee', 'oldPosition', 'newPosition', 'modifier']);
        if ($search) {
            $careerQuery->whereHas('employee', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Employees/History', [
            'careerHistories' => $careerQuery->latest('created_at')->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hire_date' => 'required|date',
            'position_id' => 'required|exists:positions,id',
            'salary_base' => 'required|numeric|min:0',
            'status' => 'required|in:actif,suspendu,inactif',
        ]);

        // Attribution automatique d'un matricule unique (WIP-2024-XXXX)
        $year = date('Y', strtotime($validated['hire_date']));
        $count = Employee::whereYear('hire_date', $year)->count() + 1;
        $matricule = "WIP-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Ensure unique
        while (Employee::where('matricule', $matricule)->exists()) {
            $count++;
            $matricule = "WIP-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $validated['matricule'] = $matricule;

        Employee::create($validated);

        return redirect()->back()->with('success', 'Employé créé avec succès avec le matricule ' . $matricule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position_id' => 'required|exists:positions,id',
            'salary_base' => 'required|numeric|min:0',
            'status' => 'required|in:actif,suspendu,inactif',
        ]);

        $employee->update($validated);

        return redirect()->back()->with('success', 'Informations mises à jour avec succès.');
    }
}
