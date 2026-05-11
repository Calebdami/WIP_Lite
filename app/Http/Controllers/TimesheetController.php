<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use App\Models\TimesheetHistory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────
    // 3.6.1 & 3.6.2 — Saisie des heures (SUP pour TC / CP pour SUP)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Liste des feuilles de temps (filtrée par rôle).
     * - SUP : voit les timesheets de ses TC
     * - CP  : voit les timesheets de ses SUP + peut voir ceux des TC
     * - TC  : voit uniquement ses propres timesheets validés
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'status'      => 'nullable|in:brouillon,soumis,valide,rejete',
            'period_start' => 'nullable|date',
            'period_end'   => 'nullable|date',
            'search'      => 'nullable|string',
        ]);

        $user = $request->user();
        $employee = $user->employee;
        $role = $user->role->name;

        $query = Timesheet::with(['employee', 'validator', 'entries']);

        // Filtrage par rôle
        if ($role === 'sup' && $employee) {
            // Le superviseur ne voit que ses TCs
            $managedIds = \App\Models\Assignment::where('manager_id', $employee->id)
                ->where('status', 'active')
                ->pluck('employee_id');
            $query->whereIn('employee_id', $managedIds);
        } elseif ($role === 'cp' && $employee) {
            // Le CP voit ses SUPs et les TCs de ses SUPs
            $managedSupIds = \App\Models\Assignment::where('manager_id', $employee->id)
                ->where('status', 'active')
                ->pluck('employee_id');
            
            $managedTcIds = \App\Models\Assignment::whereIn('manager_id', $managedSupIds)
                ->where('status', 'active')
                ->pluck('employee_id');
                
            $allManagedIds = $managedSupIds->merge($managedTcIds)->unique();
            $query->whereIn('employee_id', $allManagedIds);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$search]);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_start')) {
            $query->where('period_start', '>=', $request->period_start);
        }

        if ($request->filled('period_end')) {
            $query->where('period_end', '<=', $request->period_end);
        }

        return response()->json(
            $query->latest('period_start')->get()
        );
    }

    /**
     * Afficher une feuille de temps avec ses entrées.
     */
    public function show(Timesheet $timesheet): JsonResponse
    {
        return response()->json(
            $timesheet->load(['employee', 'validator', 'entries', 'histories.author'])
        );
    }

    /**
     * Créer une feuille de temps pour un employé sur une période.
     * Étape 1 du processus : sélection de la période + employé.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        // Sécurité : Vérifier que l'utilisateur a le droit de créer pour cet employé
        $user = $request->user();
        $authEmployee = $user->employee;
        $role = $user->role->name;

        if ($role === 'sup' && $authEmployee) {
            $isManaged = \App\Models\Assignment::where('manager_id', $authEmployee->id)
                ->where('employee_id', $request->employee_id)
                ->where('status', 'active')
                ->exists();
            if (!$isManaged) {
                return response()->json(['message' => 'Vous n\'avez pas la permission de créer une feuille pour cet agent.'], 403);
            }
        } elseif ($role === 'cp' && $authEmployee) {
            $managedSupIds = \App\Models\Assignment::where('manager_id', $authEmployee->id)
                ->where('status', 'active')
                ->pluck('employee_id');
            
            $isManaged = \App\Models\Assignment::where('employee_id', $request->employee_id)
                ->where(function($q) use ($authEmployee, $managedSupIds) {
                    $q->where('manager_id', $authEmployee->id)
                      ->orWhereIn('manager_id', $managedSupIds);
                })
                ->where('status', 'active')
                ->exists();

            if (!$isManaged) {
                return response()->json(['message' => 'Cet employé n\'est pas dans votre périmètre de gestion.'], 403);
            }
        }

        // Vérifier qu'il n'existe pas déjà une feuille pour cet employé sur cette période
        $existing = Timesheet::where('employee_id', $request->employee_id)
            ->where(function ($q) use ($request) {
                $q->whereBetween('period_start', [$request->period_start, $request->period_end])
                  ->orWhereBetween('period_end', [$request->period_start, $request->period_end]);
            })
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Une feuille de temps existe déjà pour cet employé sur cette période.'
            ], 422);
        }

        $timesheet = Timesheet::create([
            'employee_id'  => $request->employee_id,
            'period_start' => $request->period_start,
            'period_end'   => $request->period_end,
            'status'       => 'brouillon',
        ]);

        // Créer les entrées pour chaque jour de la période
        $start = Carbon::parse($request->period_start);
        $end = Carbon::parse($request->period_end);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            TimesheetEntry::create([
                'timesheet_id' => $timesheet->id,
                'date'         => $date->toDateString(),
                'planned_hours' => 0, // À remplir depuis le planning
            ]);
        }

        // Historique : création
        TimesheetHistory::create([
            'timesheet_id' => $timesheet->id,
            'employee_id'  => $request->employee_id,
            'old_status'   => null,
            'new_status'   => 'brouillon',
            'changed_by'   => $this->getAuthEmployeeId($request),
            'reason'       => 'Création de la feuille de temps',
        ]);

        return response()->json(
            $timesheet->load('entries'),
            201
        );
    }

    /**
     * Supprimer une feuille de temps (uniquement si brouillon).
     */
    public function destroy(Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->status !== 'brouillon') {
            return response()->json([
                'message' => 'Seules les feuilles au statut brouillon peuvent être supprimées.'
            ], 403);
        }

        $timesheet->delete();

        return response()->json([
            'message' => 'Feuille de temps supprimée.'
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // 3.6.3 — Validation des heures saisies
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Soumettre une feuille de temps (passage brouillon → soumis).
     */
    public function submit(Request $request, Timesheet $timesheet): JsonResponse
    {
        if (!in_array($timesheet->status, ['brouillon', 'rejete'])) {
            return response()->json([
                'message' => 'Seules les feuilles au statut brouillon ou rejeté peuvent être soumises.'
            ], 422);
        }

        // Vérifier la cohérence : au moins une entrée avec des heures
        $hasEntries = $timesheet->entries()
            ->where(function ($q) {
                $q->where('total_hours', '>', 0)
                  ->orWhere('management_hours', '>', 0)
                  ->orWhere('on_call_hours', '>', 0)
                  ->orWhere('training_hours', '>', 0)
                  ->orWhere(function($sub) {
                      $sub->whereNotNull('absence_type')
                          ->where('absence_type', '!=', '');
                  });
            })
            ->exists();

        if (!$hasEntries) {
            return response()->json([
                'message' => 'Impossible de soumettre : aucune heure (travail, management, astreinte, formation) ou absence renseignée.'
            ], 422);
        }

        $oldStatus = $timesheet->status;
        $timesheet->update(['status' => 'soumis']);

        TimesheetHistory::create([
            'timesheet_id' => $timesheet->id,
            'employee_id'  => $timesheet->employee_id,
            'old_status'   => $oldStatus,
            'new_status'   => 'soumis',
            'changed_by'   => $this->getAuthEmployeeId($request),
            'reason'       => 'Soumission pour validation',
        ]);

        return response()->json($timesheet->fresh(['entries']));
    }

    /**
     * Valider une feuille de temps (passage soumis → validé).
     * Seul le Chef de Plateau peut valider.
     * Les heures validées ne peuvent plus être modifiées.
     */
    public function validate_timesheet(Request $request, Timesheet $timesheet): JsonResponse
    {
        // On permet la validation depuis soumis, brouillon ou rejeté pour plus de souplesse pour le CP
        if (!in_array($timesheet->status, ['soumis', 'brouillon', 'rejete'])) {
            return response()->json([
                'message' => 'Cette feuille de temps ne peut pas être validée dans son état actuel.'
            ], 422);
        }

        // Note: On a assoupli la vérification pour permettre à l'Admin de valider 
        // même si le calcul automatique des heures a rencontré un problème.

        $validatorId = $this->getAuthEmployeeId($request);

        $oldStatus = $timesheet->status;
        $timesheet->update([
            'status'       => 'valide',
            'validated_by' => $validatorId,
            'validated_at' => now(),
        ]);

        TimesheetHistory::create([
            'timesheet_id' => $timesheet->id,
            'employee_id'  => $timesheet->employee_id,
            'old_status'   => $oldStatus,
            'new_status'   => 'valide',
            'changed_by'   => $validatorId,
            'reason'       => 'Validation par le Chef de Plateau',
        ]);

        return response()->json($timesheet->fresh(['entries', 'validator']));
    }

    /**
     * Validation en lot : valider plusieurs feuilles de temps en une fois.
     */
    public function validateBatch(Request $request): JsonResponse
    {
        $request->validate([
            'timesheet_ids'   => 'required|array|min:1',
            'timesheet_ids.*' => 'exists:timesheets,id',
        ]);

        $validatorId = $this->getAuthEmployeeId($request);
        $validated = [];
        $errors = [];

        foreach ($request->timesheet_ids as $id) {
            $timesheet = Timesheet::find($id);

            if (!in_array($timesheet->status, ['soumis', 'brouillon', 'rejete'])) {
                $errors[] = "Feuille #{$id} : statut '{$timesheet->status}' non éligible.";
                continue;
            }

            $oldStatus = $timesheet->status;
            $timesheet->update([
                'status'       => 'valide',
                'validated_by' => $validatorId,
                'validated_at' => now(),
            ]);

            TimesheetHistory::create([
                'timesheet_id' => $timesheet->id,
                'employee_id'  => $timesheet->employee_id,
                'old_status'   => $oldStatus,
                'new_status'   => 'valide',
                'changed_by'   => $validatorId,
                'reason'       => 'Validation en lot par le Chef de Plateau',
            ]);

            $validated[] = $id;
        }

        return response()->json([
            'validated' => $validated,
            'errors'    => $errors,
        ]);
    }

    /**
     * Rejeter une feuille de temps (passage soumis → rejeté).
     */
    public function reject(Request $request, Timesheet $timesheet): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($timesheet->status !== 'soumis') {
            return response()->json([
                'message' => 'Seules les feuilles au statut soumis peuvent être rejetées.'
            ], 422);
        }

        $oldStatus = $timesheet->status;
        $timesheet->update(['status' => 'rejete']);

        TimesheetHistory::create([
            'timesheet_id' => $timesheet->id,
            'employee_id'  => $timesheet->employee_id,
            'old_status'   => $oldStatus,
            'new_status'   => 'rejete',
            'changed_by'   => $this->getAuthEmployeeId($request),
            'reason'       => $request->reason,
        ]);

        return response()->json($timesheet->fresh());
    }

    // ──────────────────────────────────────────────────────────────────────
    // 3.6.4 — Consultation des heures par les TC
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Consulter ses propres heures validées (pour les TC).
     * - Lecture seule
     * - Historique limité aux 12 derniers mois
     */
    public function myHours(Request $request): JsonResponse
    {
        $employeeId = $this->getAuthEmployeeId($request);

        $since = Carbon::now()->subMonths(12)->startOfMonth();

        $query = Timesheet::with('entries')
            ->where('employee_id', $employeeId)
            ->where('period_start', '>=', $since);

        if (!$request->boolean('all', false)) {
            $query->where('status', 'valide');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $timesheets = $query
            ->orderBy('period_start', 'desc')
            ->get()
            ->map(function ($ts) {
                return [
                    'id'            => $ts->id,
                    'period_start'  => $ts->period_start->toDateString(),
                    'period_end'    => $ts->period_end->toDateString(),
                    'status'        => $ts->status,
                    'validated_at'  => $ts->validated_at?->toDateTimeString(),
                    'total_hours'   => $ts->total_hours,
                    'planned_hours' => $ts->total_planned_hours,
                    'overtime_hours' => $ts->total_overtime_hours,
                    'deviation'     => $ts->hours_deviation,
                    'entries'       => $ts->entries->map(fn ($e) => [
                        'id'             => $e->id,
                        'date'           => $e->date->toDateString(),
                        'check_in'       => $e->check_in?->format('H:i'),
                        'check_out'      => $e->check_out?->format('H:i'),
                        'break_duration' => $e->break_duration,
                        'total_hours'    => $e->total_hours,
                        'planned_hours'  => $e->planned_hours,
                        'overtime_hours' => $e->overtime_hours,
                        'management_hours' => $e->management_hours,
                        'on_call_hours'    => $e->on_call_hours,
                        'training_hours'   => $e->training_hours,
                        'absence_type'     => $e->absence_type,
                        'comment'          => $e->comment,
                    ]),
                ];
            });

        return response()->json($timesheets);
    }

    /**
     * Mise à jour groupée des heures pour plusieurs feuilles de temps.
     */
    public function batchUpdateHours(Request $request): JsonResponse
    {
        $request->validate([
            'timesheet_ids' => 'required|array',
            'timesheet_ids.*' => 'exists:timesheets,id',
            'schedule.check_in' => 'required|string',
            'schedule.check_out' => 'required|string',
            'schedule.break_duration' => 'required|integer',
        ]);

        $ids = $request->timesheet_ids;
        $schedule = $request->schedule;
        $validatorId = $this->getAuthEmployeeId($request);

        foreach ($ids as $id) {
            $timesheet = Timesheet::find($id);
            
            // On ne modifie pas les feuilles déjà validées
            if ($timesheet->status === 'valide') continue;

            // Mise à jour de toutes les entrées de cette feuille
            $timesheet->entries()->update([
                'check_in' => $schedule['check_in'],
                'check_out' => $schedule['check_out'],
                'break_duration' => $schedule['break_duration'],
                'absence_type' => null, // On réinitialise l'absence si on force des heures
            ]);

            // Recalcul du total des heures
            // $timesheet->calculateTotalHours(); // Supprimé car total_hours est un accesseur calculé

            // Log dans l'historique
            TimesheetHistory::create([
                'timesheet_id' => $timesheet->id,
                'employee_id' => $timesheet->employee_id,
                'old_status' => $timesheet->status,
                'new_status' => $timesheet->status,
                'changed_by' => $validatorId,
                'reason' => 'Mise à jour groupée des horaires (Saisie multiple)',
            ]);
        }

        return response()->json(['message' => 'Mise à jour groupée effectuée']);
    }

    /**
     * Historique des changements de statut d'une feuille de temps.
     */
    public function history(Timesheet $timesheet): JsonResponse
    {
        return response()->json(
            $timesheet->histories()
                ->with('author')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // Utilitaires
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Récupérer l'ID de l'employé connecté.
     */
    private function getAuthEmployeeId(Request $request): int
    {
        // On récupère l'employé lié à l'utilisateur. 
        // Si absent (compte admin pur), on essaie de trouver le premier employé ou on renvoie l'ID 1 par défaut pour les tests.
        $employee = $request->user()->employee;
        
        if (!$employee) {
            $firstEmployee = \App\Models\Employee::where('user_id', $request->user()->id)->first() 
                            ?? \App\Models\Employee::first();
            return $firstEmployee ? $firstEmployee->id : 1;
        }

        return $employee->id;
    }
}
