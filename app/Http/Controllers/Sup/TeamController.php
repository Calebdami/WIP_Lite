<?php

namespace App\Http\Controllers\Sup;

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
            return Inertia::render('Sup/Team/Index', [
                'campaigns' => []
            ]);
        }

        // 1. Récupérer les campagnes actives du superviseur
        $supAssignments = Assignment::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->with('campaign')
            ->get();

        // 2. Pour chaque campagne, récupérer les téléconseillers affectés à ce superviseur
        $campaigns = $supAssignments->map(function ($supAssign) use ($employee) {
            $tcs = Assignment::where('manager_id', $employee->id)
                ->where('campaign_id', $supAssign->campaign_id)
                ->where('status', 'active')
                ->with(['employee.position'])
                ->get();

            return [
                'id' => $supAssign->id,
                'campaign' => $supAssign->campaign,
                'tcs' => $tcs
            ];
        });

        return Inertia::render('Sup/Team/Index', [
            'campaigns' => $campaigns
        ]);
    }
}
