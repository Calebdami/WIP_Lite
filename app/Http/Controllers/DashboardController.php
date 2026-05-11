<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Campaign;
use App\Models\User;
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

        $recentActivities = PlanningHistorys::with(['assignment.employee', 'author'])
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'time' => $log->created_at ? Carbon::parse($log->created_at)->format('H:i') : '--:--',
                    'action' => $log->new_status ? "Statut passé à: " . ucfirst($log->new_status) : "Action planning",
                    'user' => $log->author?->name ?? ($log->author?->email ? explode('@', $log->author->email)[0] : 'Système'),
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
        $campaignsCount = Campaign::count();
        $totalEmployees = Employee::count();
        
        return Inertia::render('Cp/Dashboard', [
            'stats' => [
                'campaigns' => $campaignsCount,
                'employees' => $totalEmployees,
                'alerts' => 0
            ]
        ]);
    }

    /**
     * SUP (Superviseur) Dashboard Data
     */
    private function supDashboard()
    {
        return Inertia::render('Sup/Dashboard', [
            'stats' => [
                'team_size' => 0,
                'active_sessions' => 0
            ]
        ]);
    }

    /**
     * TC (Téléconseiller) Dashboard Data
     */
    private function tcDashboard()
    {
        $user = Auth::user();
        // Assume TC user is linked to an employee via email or a specific relation
        $employee = Employee::where('email', $user->email)->first();
        
        $currentCampaign = $employee ? $employee->campaign?->name : 'Non affecté';
        
        // Example: Hours worked this week
        $startOfWeek = Carbon::now()->startOfWeek();
        $hoursThisWeek = 0;
        if ($employee) {
            $hoursThisWeek = TimesheetEntry::whereHas('timesheet', function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->where('date', '>=', $startOfWeek)
            ->sum('total_hours');
        }

        return Inertia::render('Tc/Dashboard', [
            'personal_stats' => [
                'campaign' => $currentCampaign,
                'hours_week' => round($hoursThisWeek, 1),
                'supervisor' => $employee?->manager?->first_name . ' ' . $employee?->manager?->last_name ?? 'N/A'
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
