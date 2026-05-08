<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScoringController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::withSum(['tasks as total_score' => function($q) {
            $q->where('status', 'validated');
        }], 'points')->with(['position']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Admin/Scoring/Index', [
            'employees' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Employee $employee)
    {
        return Inertia::render('Admin/Scoring/Show', [
            'employee' => $employee->load(['position']),
            'tasks' => $employee->tasks()->latest()->get(),
        ]);
    }

    public function validateTask(Request $request, Task $task)
    {
        $task->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tâche validée. Le score a été mis à jour.');
    }
    
    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'points' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);
        
        Task::create($validated);
        
        return redirect()->back()->with('success', 'Tâche ajoutée avec succès.');
    }
}
