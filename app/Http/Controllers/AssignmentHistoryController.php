<?php

namespace App\Http\Controllers;

use App\Models\AssignmentHistory;
use Illuminate\Http\Request;

class AssignmentHistoryController extends Controller
{
    /**
     * Liste de tout l'historique
     */
    public function index()
    {
        return response()->json(
            AssignmentHistory::with([
                'assignment',
                'employee',
                'changer',
                'oldManager',
                'newManager',
                'oldCampaign',
                'newCampaign'
            ])->latest()->get()
        );
    }

    /**
     * Voir un historique précis
     */
    public function show(AssignmentHistory $assignmentHistory)
    {
        return response()->json(
            $assignmentHistory->load([
                'assignment',
                'employee',
                'changer',
                'oldManager',
                'newManager',
                'oldCampaign',
                'newCampaign'
            ])
        );
    }

    /**
     * Suppression (option admin uniquement)
     */
    public function destroy(AssignmentHistory $assignmentHistory)
    {
        $assignmentHistory->delete();

        return response()->json([
            'message' => 'Historique supprimé avec succès'
        ]);
    }

    // inutiles
    public function create() {}
    public function store(Request $request) {}
    public function edit(AssignmentHistory $assignmentHistory) {}
    public function update(Request $request, AssignmentHistory $assignmentHistory) {}
}