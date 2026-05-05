<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Liste des logs (avec utilisateur)
     */
    public function index()
    {
        return response()->json(
            ActivityLog::with('user')->latest()->get()
        );
    }

    /**
     * Afficher un log précis
     */
    public function show(ActivityLog $activityLog)
    {
        return response()->json(
            $activityLog->load('user')
        );
    }

    /**
     * Supprimer un log (option admin)
     */
    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();

        return response()->json([
            'message' => 'Log supprimé avec succès'
        ]);
    }

    // inutiles 
    public function create() {}
    public function edit(ActivityLog $activityLog) {}
    public function store(Request $request) {}
    public function update(Request $request, ActivityLog $activityLog) {}
}