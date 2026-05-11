<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Assignment;
use App\Models\PlanningHistorys;
use App\Models\PlanningAssignment;
use App\Models\TimesheetEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role?->name ?? 'tc';

        return match ($role) {
            'admin' => $this->adminDashboard(),
            'cp'    => $this->cpDashboard(),
            'sup'   => $this->supDashboard(),
            'tc'    => $this->tcDashboard(),
            default => Inertia::render('Dashboard'),
        };
    }

    /**
     * Admin Dashboard Data
     */
    private function adminDashboard()
    {
        $activeEmployeesCount = Employee::where('status', 'actif')->count();
        $campaignsCount = Campaign::count();
        $totalUsersCount = User::count();
        
        $totalEmployees = Employee::count();
        $assignedEmployeesCount = PlanningAssignment::distinct('employee_id')->count();
        $assignmentRate = $totalEmployees > 0 ? round(($assignedEmployeesCount / $totalEmployees) * 100) : 0;

        $recentActivities = PlanningHistorys::with(['assignment.employee', 'author.employee'])
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($log) {
                $authorName = 'Système';
                if ($log->author) {
                    if ($log->author->employee) {
                        $authorName = $log->author->employee->first_name . ' ' . $log->author->employee->last_name;
                    } else {
                        $authorName = explode('@', $log->author->email)[0];
                    }
                }

                return [
                    'time' => $log->created_at ? Carbon::parse($log->created_at)->format('H:i') : '--:--',
                    'action' => $log->new_status ? "Statut passé à: " . ucfirst($log->new_status) : "Action planning",
                    'user' => $authorName,
                    'badge' => $this->getBadgeType($log->new_status)
                ];
            });

        $activeCampaigns = Campaign::withCount('employees')->take(3)->get()->map(function($c) {
            return [
                'name' => $c->name,
                'persons' => $c->employees_count,
                'progress' => 75 // Logic for progress could be added later
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'active_employees' => number_format($activeEmployeesCount),
                'campaigns_count' => $campaignsCount,
                'assignment_rate' => $assignmentRate . '%',
                'users_count' => $totalUsersCount
            ],
            'recent_activities' => $recentActivities,
            'active_campaigns' => $activeCampaigns
        ]);
    }

    /**
     * CP (Chef de Plateau) Dashboard Data
     */
    private function cpDashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return Inertia::render('Cp/Dashboard', ['stats' => ['campaigns' => 0, 'employees' => 0, 'alerts' => 0]]);
        }

        // Campagnes gérées par ce CP (via ses SUPs)
        $managedSupIds = Assignment::where('manager_id', $employee->id)
            ->where('status', 'active')
            ->pluck('employee_id');
            
        $campaignIds = Assignment::whereIn('employee_id', $managedSupIds)
            ->where('status', 'active')
            ->pluck('campaign_id')
            ->unique();
            
        $campaignsCount = Campaign::whereIn('id', $campaignIds)->count();

        // Employés gérés (SUPs + TCs des SUPs)
        $managedTcIds = Assignment::whereIn('manager_id', $managedSupIds)
            ->where('status', 'active')
            ->pluck('employee_id');
            
        $totalEmployees = $managedSupIds->count() + $managedTcIds->count();

        // Alertes : Plannings en attente de validation pour son périmètre
        $allManagedIds = $managedSupIds->merge($managedTcIds)->unique();
        $alerts = PlanningAssignment::whereIn('employee_id', $allManagedIds)
            ->where('status', 'en attente')
            ->count();

        // Activités récentes dans son périmètre
        $recentActivities = PlanningHistorys::whereHas('assignment', function($q) use ($allManagedIds) {
                $q->whereIn('employee_id', $allManagedIds);
            })
            ->with(['assignment.employee', 'author'])
            ->latest()
            ->take(5)
            ->get();
        
        return Inertia::render('Cp/Dashboard', [
            'stats' => [
                'campaigns' => $campaignsCount,
                'employees' => $totalEmployees,
                'alerts' => $alerts
            ],
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * SUP (Superviseur) Dashboard Data
     */
    private function supDashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return Inertia::render('Sup/Dashboard', ['stats' => ['team_size' => 0, 'active_sessions' => 0, 'pending_plannings' => 0]]);
        }

        // Taille de l'équipe (TCs directs)
        $teamIds = Assignment::where('manager_id', $employee->id)
            ->where('status', 'active')
            ->pluck('employee_id');
            
        $teamSize = $teamIds->count();

        // Plannings en attente pour son équipe
        $pendingPlannings = PlanningAssignment::whereIn('employee_id', $teamIds)
            ->where('status', 'en attente')
            ->count();

        // Sessions actives (MOCK: Agents avec statut actif pour le moment)
        $activeSessions = Employee::whereIn('id', $teamIds)
            ->where('status', 'actif')
            ->count();

        return Inertia::render('Sup/Dashboard', [
            'stats' => [
                'team_size' => $teamSize,
                'active_sessions' => $activeSessions,
                'pending_plannings' => $pendingPlannings
            ]
        ]);
    }

    /**
     * TC (Téléconseiller) Dashboard Data
     */
    private function tcDashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return Inertia::render('Tc/Dashboard', [
                'personal_stats' => [
                    'campaign' => 'Non affecté',
                    'hours_week' => 0,
                    'supervisor' => 'N/A'
                ]
            ]);
        }

        $activeAssignment = Assignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->with(['campaign', 'manager'])
            ->first();
            
        $currentCampaign = $activeAssignment?->campaign?->name ?? 'Non affecté';
        $supervisorName = $activeAssignment?->manager 
            ? ($activeAssignment->manager->first_name . ' ' . $activeAssignment->manager->last_name)
            : 'N/A';
        
        // Heures travaillées cette semaine
        $startOfWeek = Carbon::now()->startOfWeek();
        $hoursThisWeek = TimesheetEntry::whereHas('timesheet', function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->where('date', '>=', $startOfWeek)
            ->sum('total_hours');

        return Inertia::render('Tc/Dashboard', [
            'personal_stats' => [
                'campaign' => $currentCampaign,
                'hours_week' => round($hoursThisWeek, 1),
                'supervisor' => $supervisorName
            ]
        ]);
    }

    /**
     * Map status to UI badge types
     */
    private function getBadgeType($status)
    {
        $status = strtolower($status);
        if (str_contains($status, 'valid') || str_contains($status, 'actif')) return 'success';
        if (str_contains($status, 'attente')) return 'warning';
        if (str_contains($status, 'suspendu') || str_contains($status, 'clôt')) return 'danger';
        return 'info';
    }
}
