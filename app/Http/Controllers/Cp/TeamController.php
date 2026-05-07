<?php

namespace App\Http\Controllers\Cp;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return Inertia::render('Cp/Teams/Index', [
                'supervisors' => []
            ]);
        }

        // 1. Récupérer les campagnes actives du CP
        $cpAssignments = Assignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->pluck('campaign_id');

        // 2. Récupérer les superviseurs affectés à ce CP (dans ses campagnes)
        // Note: manager_id dans Assignment pointe vers l'employee_id du manager
        $supervisors = Assignment::where('manager_id', $employee->id)
            ->whereIn('campaign_id', $cpAssignments)
            ->where('status', 'active')
            ->with(['employee.position', 'campaign'])
            ->get()
            ->map(function ($supAssignment) {
                // 3. Pour chaque superviseur, récupérer ses téléconseillers (dans la même campagne)
                $tcs = Assignment::where('manager_id', $supAssignment->employee_id)
                    ->where('campaign_id', $supAssignment->campaign_id)
                    ->where('status', 'active')
                    ->with(['employee.position'])
                    ->get();

                return [
                    'id' => $supAssignment->id,
                    'employee' => $supAssignment->employee,
                    'campaign' => $supAssignment->campaign,
                    'tcs' => $tcs
                ];
            });

        return Inertia::render('Cp/Teams/Index', [
            'supervisors' => $supervisors
        ]);
    }
}
