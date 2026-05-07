<?php

namespace App\Http\Controllers;

use App\Models\PlanningModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

class PlanningModelController extends Controller
{
    public function index(Request $request)
    {
        $query = PlanningModel::with('creator')
            ->withCount(['assignments' => function ($query) {
                $query->where('status', 'validé')
                      ->where(function ($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', today());
                      });
            }])
            ->withMax('assignments as latest_assignment_date', 'created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $models = $query->orderByDesc('latest_assignment_date')
            ->orderBy('name', 'asc')
            ->paginate(12)
            ->withQueryString();

        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/Templates' : 'Admin/Assignments/Schedules';

        return Inertia::render($view, [
            'models' => $models,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/CreateTemplate' : 'Admin/Assignments/CreateTemplate';
        return Inertia::render($view);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hours_summary' => 'nullable|string|max:255',
            'monday_hours' => 'numeric|min:0|max:24',
            'tuesday_hours' => 'numeric|min:0|max:24',
            'wednesday_hours' => 'numeric|min:0|max:24',
            'thursday_hours' => 'numeric|min:0|max:24',
            'friday_hours' => 'numeric|min:0|max:24',
            'saturday_hours' => 'numeric|min:0|max:24',
            'sunday_hours' => 'numeric|min:0|max:24',
        ]);

        // Calcul automatique du total
        $totalHours = array_sum([
            $request->monday_hours, $request->tuesday_hours, $request->wednesday_hours,
            $request->thursday_hours, $request->friday_hours, $request->saturday_hours, $request->sunday_hours
        ]);

        PlanningModel::create(array_merge($validated, [
            'total_hours' => $totalHours,
            'created_by' => Auth::id()
        ]));

        $route = Auth::user()->role->name === 'cp' ? 'cp.schedules.templates' : 'admin.assignments.schedules';
        return redirect()->route($route)->with('success', 'Modèle de planning créé avec succès.');
    }

    public function edit(PlanningModel $planningModel)
    {
        $view = Auth::user()->role->name === 'cp' ? 'Cp/Schedules/EditTemplate' : 'Admin/Assignments/EditTemplate';
        return Inertia::render($view, [
            'model' => $planningModel
        ]);
    }

    public function update(Request $request, PlanningModel $planningModel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hours_summary' => 'nullable|string|max:255',
            'monday_hours' => 'numeric|min:0|max:24',
            'tuesday_hours' => 'numeric|min:0|max:24',
            'wednesday_hours' => 'numeric|min:0|max:24',
            'thursday_hours' => 'numeric|min:0|max:24',
            'friday_hours' => 'numeric|min:0|max:24',
            'saturday_hours' => 'numeric|min:0|max:24',
            'sunday_hours' => 'numeric|min:0|max:24',
        ]);

        $totalHours = array_sum([
            $validated['monday_hours'], $validated['tuesday_hours'], $validated['wednesday_hours'],
            $validated['thursday_hours'], $validated['friday_hours'], $validated['saturday_hours'], $validated['sunday_hours']
        ]);

        $planningModel->update(array_merge($validated, [
            'total_hours' => $totalHours
        ]));

        $route = Auth::user()->role->name === 'cp' ? 'cp.schedules.templates' : 'admin.assignments.schedules';
        return redirect()->route($route)->with('success', 'Modèle de planning mis à jour avec succès.');
    }

    public function destroy(PlanningModel $planningModel)
    {
        if ($planningModel->assignments()->exists()) {
            return back()->with('error', 'Ce modèle ne peut pas être supprimé car il est utilisé dans des affectations actives.');
        }

        $planningModel->delete();

        $route = Auth::user()->role->name === 'cp' ? 'cp.schedules.templates' : 'admin.assignments.schedules';
        return redirect()->route($route)->with('success', 'Modèle de planning supprimé avec succès.');
    }

    public function show(PlanningModel $planningModel)
    {
        return response()->json($planningModel->load('assignments'));
    }
}