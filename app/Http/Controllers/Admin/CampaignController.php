<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Campaigns/Index', [
            'campaigns' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,finished',
        ]);

        Campaign::create($validated);

        return redirect()->back()->with('success', 'Campagne créée avec succès.');
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,finished',
        ]);

        $campaign->update($validated);

        return redirect()->back()->with('success', 'Campagne mise à jour avec succès.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->assignments()->update([
            'status' => 'terminated',
            'end_date' => now()
        ]);

        $campaign->delete();

        return redirect()->back()->with('success', 'Campagne supprimée et tous les employés ont été désaffectés.');
    }
}
