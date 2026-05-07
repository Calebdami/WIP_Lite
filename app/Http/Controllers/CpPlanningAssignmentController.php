<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Assignment;
use App\Models\PlanningModel;
use App\Models\PlanningAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CpPlanningAssignmentController extends Controller
{
    /**
     * Affiche la page d'affectation de plannings aux TC
     * pour le Chef de Plateau connecté.
     */
    public function createTC()
    {
        // Récupérer tous les TC (téléconseillers)
        $tcs = Employee::whereHas('position', function ($q) {
            $q->where('code', 'TC');
        })
        ->with(['assignments' => function ($q) {
            $q->where('status', 'actif')->with(['campaign', 'manager']);
        }])
        ->get()
        ->map(function ($emp) {
            $activeAssignment = $emp->assignments->first();

            // Vérifier si ce TC a déjà un planning actif/validé en cours
            $hasActivePlanning = PlanningAssignment::where('employee_id', $emp->id)
                ->where('status', 'validé')
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->exists();

            // Récupérer le nom du superviseur
            $supervisorName = null;
            if ($activeAssignment && $activeAssignment->supervisor) {
                $supervisorName = $activeAssignment->supervisor->first_name . ' ' . $activeAssignment->supervisor->last_name;
            } elseif ($activeAssignment) {
                // Essayer via manager_id
                $manager = Employee::find($activeAssignment->manager_id ?? null);
                $supervisorName = $manager ? $manager->first_name . ' ' . $manager->last_name : null;
            }

            return [
                'id'                 => $emp->id,
                'name'               => $emp->first_name . ' ' . $emp->last_name,
                'matricule'          => $emp->matricule,
                'campaign'           => $activeAssignment?->campaign?->name ?? 'Aucune campagne',
                'supervisor'         => $supervisorName ?? 'Non assigné',
                'has_active_planning'=> $hasActivePlanning,
            ];
        });

        $models = PlanningModel::orderBy('name', 'asc')->get()->map(function ($model) {
            return [
                'id'          => $model->id,
                'name'        => $model->name,
                'description' => $model->description,
                'total_hours' => $model->total_hours,
            ];
        });

        return Inertia::render('Cp/Schedules/AssignTC', [
            'tcs'    => $tcs,
            'models' => $models,
        ]);
    }

    /**
     * Enregistre les affectations de planning pour plusieurs TC.
     */
    public function storeTCBulk(Request $request)
    {
        $validated = $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'required|exists:employees,id',
            'planning_model_id' => 'required|exists:planning_models,id',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['employee_ids'] as $employeeId) {
                PlanningAssignment::create([
                    'employee_id'       => $employeeId,
                    'planning_model_id' => $validated['planning_model_id'],
                    'start_date'        => $validated['start_date'],
                    'end_date'          => $validated['end_date'] ?? null,
                    'status'            => 'en attente',
                ]);
            }
        });

        return redirect()
            ->route('cp.schedules.assign-tc')
            ->with('success', count($validated['employee_ids']) . ' affectation(s) créée(s) avec succès.');
    }
}
