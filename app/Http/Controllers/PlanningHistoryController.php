<?php

namespace App\Http\Controllers;

use App\Models\PlanningHistorys;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningHistoryController extends Controller
{
    /**
     * Liste tout l'historique (pour un admin)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isCp = $user->role->name === 'cp';
        $cpEmployeeId = $isCp ? \App\Models\Employee::where('user_id', $user->id)->first()?->id : null;

        $query = PlanningHistorys::with(['assignment.employee', 'assignment.planningModel', 'author']);

        if ($isCp && $cpEmployeeId) {
            // Identifier tout le périmètre du CP
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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('assignment.employee', function($eq) use ($search) {
                    $eq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $history = $query->latest()->paginate(15)->withQueryString();

        // Stats filtrées pour CP
        $statsQuery = PlanningHistorys::query();
        if ($isCp && isset($allManagedIds)) {
            $statsQuery->whereHas('assignment', function($q) use ($allManagedIds) {
                $q->whereIn('employee_id', $allManagedIds);
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'validations' => (clone $statsQuery)->where('new_status', 'validé')->count(),
            'creations' => (clone $statsQuery)->whereNull('old_status')->count(),
            'suspensions' => (clone $statsQuery)->where('new_status', 'suspendu')->count(),
        ];

        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/History' : 'Admin/Assignments/History';

        return Inertia::render($view, [
            'history' => $history,
            'stats' => $stats,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Liste l'historique spécifique à une affectation
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