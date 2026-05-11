<?php

namespace App\Http\Controllers\Sup;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return Inertia::render('Sup/Team/Index', [
                'team' => []
            ]);
        }

        // Récupérer tous les agents (TC) dont ce superviseur est le manager
        $team = Assignment::where('manager_id', $employee->id)
            ->where('status', 'active')
            ->with(['employee.position', 'campaign'])
            ->get()
            ->map(function ($assignment) {
                $agent = $assignment->employee;
                return [
                    'id' => $agent->id,
                    'matricule' => $agent->matricule,
                    'first_name' => $agent->first_name,
                    'last_name' => $agent->last_name,
                    'email' => $agent->email,
                    'phone' => $agent->phone,
                    'position' => $agent->position?->name ?? 'Téléconseiller',
                    'campaign' => $assignment->campaign?->name ?? 'N/A',
                    'status' => 'Hors ligne' // Statut par défaut (pourrait être dynamique plus tard)
                ];
            });

        return Inertia::render('Sup/Team/Index', [
            'team' => $team
        ]);
    }
}
