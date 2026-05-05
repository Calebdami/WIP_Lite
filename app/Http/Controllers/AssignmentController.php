<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Liste des affectations
     */
    public function index()
    {
        return response()->json(
            Assignment::with(['employee', 'campaign', 'manager', 'position'])
                ->latest()
                ->get()
        );
    }

    /**
     * Créer une affectation
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'campaign_id' => 'required|exists:campaigns,id',
            'manager_id' => 'nullable|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $assignment = Assignment::create([
            'employee_id' => $request->employee_id,
            'campaign_id' => $request->campaign_id,
            'manager_id' => $request->manager_id,
            'position_id' => $request->position_id,
            'status' => 'active',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json($assignment, 201);
    }

    /**
     * Afficher une affectation
     */
    public function show(Assignment $assignment)
    {
        return response()->json(
            $assignment->load(['employee', 'campaign', 'manager', 'position'])
        );
    }

    /**
     * Mettre à jour une affectation
     */
    public function update(Request $request, Assignment $assignment)
    {
        $request->validate([
            'status' => 'in:active,terminated,suspended',
            'end_date' => 'nullable|date',
        ]);

        $assignment->update($request->only([
            'status',
            'end_date'
        ]));

        return response()->json($assignment);
    }

    /**
     * Supprimer une affectation
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return response()->json([
            'message' => 'Affectation supprimée'
        ]);
    }

    // inutiles 
    public function create() {}
    public function edit(Assignment $assignment) {}
}