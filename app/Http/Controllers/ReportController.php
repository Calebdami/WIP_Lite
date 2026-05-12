<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\TimesheetEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Affiche la liste des rapports selon le rôle de l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role->name;
        $employee = Employee::where('user_id', $user->id)->first();

        $query = DailyReport::with(['employee.position', 'manager', 'campaign']);

        if ($role === 'admin') {
            // Admin voit tout
        } elseif ($role === 'cp' && $employee) {
            // CP voit ses rapports envoyés + les rapports de ses SUPs et TCs sous ses SUPs
            $managedSupIds = Assignment::where('manager_id', $employee->id)
                ->where('status', 'active')
                ->pluck('employee_id');

            $managedTcIds = Assignment::whereIn('manager_id', $managedSupIds)
                ->where('status', 'active')
                ->pluck('employee_id');

            $allSubordinateIds = $managedSupIds->merge($managedTcIds)->unique();
            
            $query->where(function($q) use ($employee, $allSubordinateIds) {
                $q->where('employee_id', $employee->id)
                  ->orWhereIn('employee_id', $allSubordinateIds);
            });
        } elseif ($role === 'sup' && $employee) {
            // SUP voit ses rapports + ceux de ses TCs
            $managedTcIds = Assignment::where('manager_id', $employee->id)
                ->where('status', 'active')
                ->pluck('employee_id');

            $query->where(function($q) use ($employee, $managedTcIds) {
                $q->where('employee_id', $employee->id)
                  ->orWhereIn('employee_id', $managedTcIds);
            });
        } else {
            // TC voit seulement les siens
            $query->where('employee_id', $employee->id ?? 0);
        }

        // Filtres
        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(15)->withQueryString();

        // Données pour le formulaire de création
        $campaigns = [];
        $manager = null;
        if ($employee) {
            $activeAssignment = Assignment::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->with(['campaign', 'manager'])
                ->first();
            
            if ($activeAssignment) {
                $manager = $activeAssignment->manager;
                $campaigns = Campaign::where('status', 'active')->get();
            }
        }

        $view = match ($role) {
            'admin' => 'Admin/Reports/Index',
            'cp'    => 'Cp/Reports/Index',
            'sup'   => 'Sup/Reports/Index',
            'tc'    => 'Tc/Reports/Index',
            default => 'Dashboard',
        };

        return Inertia::render($view, [
            'reports' => $reports,
            'campaigns' => $campaigns,
            'myManager' => $manager,
            'filters' => $request->only(['date', 'campaign_id']),
        ]);
    }

    /**
     * Enregistre un nouveau rapport journalier.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'report_date' => 'required|date|before_or_equal:today',
            'campaign_id' => 'required|exists:campaigns,id',
            'tasks_completed' => 'required|string',
            'issues' => 'nullable|string',
            'next_day_plan' => 'nullable|string',
        ]);

        // Trouver le manager direct via l'affectation active
        $activeAssignment = Assignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->first();

        $managerId = $activeAssignment ? $activeAssignment->manager_id : null;

        DailyReport::create(array_merge($validated, [
            'employee_id' => $employee->id,
            'manager_id' => $managerId,
            'status' => 'submitted',
        ]));

        return back()->with('success', 'Rapport envoyé avec succès.');
    }

    /**
     * Tableau de bord analytique.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $role = $user->role->name;
        $employee = Employee::where('user_id', $user->id)->first();

        // Définir le périmètre (scope)
        $managedEmployeeIds = [];
        if ($role === 'admin') {
            $managedEmployeeIds = Employee::pluck('id')->toArray();
        } elseif ($role === 'cp' && $employee) {
            $supIds = Assignment::where('manager_id', $employee->id)->where('status', 'active')->pluck('employee_id');
            $tcIds = Assignment::whereIn('manager_id', $supIds)->where('status', 'active')->pluck('employee_id');
            $managedEmployeeIds = $supIds->merge($tcIds)->push($employee->id)->unique()->toArray();
        } elseif ($role === 'sup' && $employee) {
            $tcIds = Assignment::where('manager_id', $employee->id)->where('status', 'active')->pluck('employee_id');
            $managedEmployeeIds = $tcIds->push($employee->id)->unique()->toArray();
        } else {
            $managedEmployeeIds = [$employee->id ?? 0];
        }

        // 1. Statistiques par campagne
        $statsByCampaign = Campaign::whereHas('assignments', function($q) use ($managedEmployeeIds) {
                $q->whereIn('employee_id', $managedEmployeeIds);
            })
            ->get()
            ->map(function($campaign) use ($managedEmployeeIds) {
                $timesheetData = DB::table('timesheet_entries')
                    ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                    ->join('assignments', function($join) {
                        $join->on('timesheets.employee_id', '=', 'assignments.employee_id')
                             ->whereColumn('timesheet_entries.date', '>=', 'assignments.start_date')
                             ->where(function($q) {
                                 $q->whereColumn('timesheet_entries.date', '<=', 'assignments.end_date')
                                   ->orWhereNull('assignments.end_date');
                             });
                    })
                    ->where('assignments.campaign_id', $campaign->id)
                    ->whereIn('timesheets.employee_id', $managedEmployeeIds)
                    ->select(
                        DB::raw('SUM(timesheet_entries.planned_hours) as total_planned'),
                        DB::raw('SUM(timesheet_entries.total_hours) as total_realized')
                    )
                    ->first();

                return [
                    'name' => $campaign->name,
                    'planned' => round((float) ($timesheetData->total_planned ?? 0), 1),
                    'realized' => round((float) ($timesheetData->total_realized ?? 0), 1),
                    'gap' => round((float) (($timesheetData->total_realized ?? 0) - ($timesheetData->total_planned ?? 0)), 1),
                ];
            });

        // 2. Évolution temporelle (7 derniers jours)
        $last7Days = collect(range(0, 6))->map(function($i) use ($managedEmployeeIds) {
            $date = Carbon::today()->subDays($i);
            $data = DB::table('timesheet_entries')
                ->join('timesheets', 'timesheet_entries.timesheet_id', '=', 'timesheets.id')
                ->whereDate('timesheet_entries.date', $date)
                ->whereIn('timesheets.employee_id', $managedEmployeeIds)
                ->select(
                    DB::raw('SUM(timesheet_entries.planned_hours) as planned'),
                    DB::raw('SUM(timesheet_entries.total_hours) as realized')
                )
                ->first();

            return [
                'date' => $date->format('d/m'),
                'planned' => round((float) ($data->planned ?? 0), 1),
                'realized' => round((float) ($data->realized ?? 0), 1),
            ];
        })->reverse()->values();

        // 3. Top employés
        $topEmployees = Employee::whereIn('employees.id', $managedEmployeeIds)
            ->leftJoin('timesheets', 'employees.id', '=', 'timesheets.employee_id')
            ->leftJoin('timesheet_entries', 'timesheets.id', '=', 'timesheet_entries.timesheet_id')
            ->select(
                'employees.first_name', 
                'employees.last_name',
                DB::raw('SUM(timesheet_entries.total_hours) as total_hours')
            )
            ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
            ->orderByDesc('total_hours')
            ->take(5)
            ->get()
            ->map(fn($emp) => [
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'hours' => round((float) ($emp->total_hours ?? 0), 1)
            ]);

        $view = match ($role) {
            'admin' => 'Admin/Reports/Dashboard',
            'cp'    => 'Cp/Reports/Dashboard',
            'sup'   => 'Sup/Reports/Dashboard',
            'tc'    => 'Tc/Reports/Dashboard',
            default => 'Dashboard',
        };

        return Inertia::render($view, [
            'statsByCampaign' => $statsByCampaign,
            'evolution' => $last7Days,
            'topEmployees' => $topEmployees,
        ]);
    }

    /**
     * Export des rapports en CSV.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        $query = DailyReport::with(['employee', 'campaign']);
        
        // Appliquer les mêmes restrictions que l'index
        if ($user->role->name !== 'admin') {
            $query->where('employee_id', $employee->id ?? 0);
        }

        $reports = $query->get();

        $filename = "rapports_" . now()->format('Y-m-d_H-i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Employé', 'Campagne', 'Tâches', 'Difficultés', 'Plan Demain'];

        $callback = function() use($reports, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->report_date->format('d/m/Y'),
                    $report->employee->first_name . ' ' . $report->employee->last_name,
                    $report->campaign->name,
                    $report->tasks_completed,
                    $report->issues,
                    $report->next_day_plan,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
