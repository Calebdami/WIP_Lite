<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TimesheetEntryController extends Controller
{
    /**
     * Saisie des heures pour un jour donné.
     * Utilisé par le SUP (pour ses TC) et le CP (pour ses SUP).
     *
     * Informations saisies :
     * - Heures d'arrivée et de départ
     * - Temps de pause effectif
     * - Heures supplémentaires éventuelles
     * - Absences et leurs motifs
     * - Commentaires particuliers
     */
    public function update(Request $request, TimesheetEntry $entry): JsonResponse
    {
        // Vérifier que la feuille est modifiable (brouillon ou rejeté)
        $timesheet = $entry->timesheet;

        if (!in_array($timesheet->status, ['brouillon', 'rejeté'])) {
            return response()->json([
                'message' => 'Les heures ne peuvent être modifiées que sur une feuille au statut brouillon ou rejeté.'
            ], 403);
        }

        $validated = $request->validate([
            'check_in'       => 'nullable|date_format:H:i',
            'check_out'      => 'nullable|date_format:H:i|after:check_in',
            'break_duration'   => 'nullable|integer|min:0|max:480',
            'planned_hours'    => 'nullable|numeric|min:0|max:24',
            'management_hours' => 'nullable|numeric|min:0|max:24',
            'on_call_hours'    => 'nullable|numeric|min:0|max:24',
            'training_hours'   => 'nullable|numeric|min:0|max:24',
            'absence_type'     => 'nullable|string|max:100',
            'comment'          => 'nullable|string|max:1000',
        ]);

        // Calcul automatique du total d'heures si check_in et check_out fournis
        $totalHours = $entry->total_hours;
        $checkIn = $validated['check_in'] ?? ($entry->check_in ? $entry->check_in->format('H:i') : null);
        $checkOut = $validated['check_out'] ?? ($entry->check_out ? $entry->check_out->format('H:i') : null);
        $breakDuration = $validated['break_duration'] ?? $entry->break_duration ?? 0;

        if ($checkIn && $checkOut) {
            try {
                $start = \Carbon\Carbon::parse($checkIn);
                $end = \Carbon\Carbon::parse($checkOut);
                $rawMinutes = $end->diffInMinutes($start);
                $totalHours = round(max(0, ($rawMinutes - $breakDuration) / 60), 2);
            } catch (\Exception $e) {
                // En cas d'erreur de parsing, on garde la valeur actuelle
            }
        }

        // Calcul automatique des heures supplémentaires
        $plannedHours = $validated['planned_hours'] ?? $entry->planned_hours ?? 0;
        $overtimeHours = round(max(0, $totalHours - $plannedHours), 2);

        // Si absence, on met les heures à 0
        if (!empty($validated['absence_type'])) {
            $totalHours = 0;
            $overtimeHours = 0;
        }

        $entry->update(array_merge($validated, [
            'total_hours'    => $totalHours,
            'overtime_hours' => $overtimeHours,
        ]));

        return response()->json($entry->fresh());
    }

    /**
     * Saisie en lot : mise à jour de plusieurs entrées d'une même feuille.
     * Utilisé pour la saisie rapide de toute la semaine.
     */
    public function batchUpdate(Request $request, Timesheet $timesheet): JsonResponse
    {
        // Vérifier que la feuille est modifiable
        if (!in_array($timesheet->status, ['brouillon', 'rejeté'])) {
            return response()->json([
                'message' => 'Les heures ne peuvent être modifiées que sur une feuille au statut brouillon ou rejeté.'
            ], 403);
        }

        $request->validate([
            'entries'                  => 'required|array|min:1',
            'entries.*.id'             => 'required|exists:timesheet_entries,id',
            'entries.*.check_in'       => 'nullable|date_format:H:i',
            'entries.*.check_out'      => 'nullable|date_format:H:i',
            'entries.*.break_duration' => 'nullable|integer|min:0|max:480',
            'entries.*.planned_hours'    => 'nullable|numeric|min:0|max:24',
            'entries.*.management_hours' => 'nullable|numeric|min:0|max:24',
            'entries.*.on_call_hours'    => 'nullable|numeric|min:0|max:24',
            'entries.*.training_hours'   => 'nullable|numeric|min:0|max:24',
            'entries.*.absence_type'     => 'nullable|string|max:100',
            'entries.*.comment'          => 'nullable|string|max:1000',
        ]);

        $updatedEntries = [];

        foreach ($request->entries as $data) {
            $entry = TimesheetEntry::where('id', $data['id'])
                ->where('timesheet_id', $timesheet->id)
                ->first();

            if (!$entry) {
                continue;
            }

            $checkIn = $data['check_in'] ?? ($entry->check_in ? $entry->check_in->format('H:i') : null);
            $checkOut = $data['check_out'] ?? ($entry->check_out ? $entry->check_out->format('H:i') : null);
            $breakDuration = $data['break_duration'] ?? $entry->break_duration ?? 0;

            // Calcul automatique
            $totalHours = $entry->total_hours;
            if ($checkIn && $checkOut) {
                try {
                    $start = \Carbon\Carbon::parse($checkIn);
                    $end = \Carbon\Carbon::parse($checkOut);
                    $rawMinutes = $end->diffInMinutes($start);
                    $totalHours = round(max(0, ($rawMinutes - $breakDuration) / 60), 2);
                } catch (\Exception $e) {
                    // Ignorer les erreurs de format
                }
            }

            $plannedHours = $data['planned_hours'] ?? $entry->planned_hours ?? 0;
            $overtimeHours = round(max(0, $totalHours - $plannedHours), 2);

            // Si absence, heures à 0
            if (!empty($data['absence_type'])) {
                $totalHours = 0;
                $overtimeHours = 0;
            }

            $entry->update([
                'check_in'       => $data['check_in'] ?? $entry->check_in,
                'check_out'      => $data['check_out'] ?? $entry->check_out,
                'break_duration' => $breakDuration,
                'planned_hours'  => $plannedHours,
                'total_hours'    => $totalHours,
                'overtime_hours' => $overtimeHours,
                'management_hours' => $data['management_hours'] ?? $entry->management_hours ?? 0,
                'on_call_hours'    => $data['on_call_hours'] ?? $entry->on_call_hours ?? 0,
                'training_hours'   => $data['training_hours'] ?? $entry->training_hours ?? 0,
                'absence_type'     => $data['absence_type'] ?? $entry->absence_type,
                'comment'          => $data['comment'] ?? $entry->comment,
            ]);

            $updatedEntries[] = $entry->fresh();
        }

        return response()->json([
            'entries' => $updatedEntries,
            'timesheet' => $timesheet->fresh(['entries']),
        ]);
    }

    /**
     * Afficher les entrées d'une feuille de temps avec comparaison planning.
     * Inclut la détection des écarts et des anomalies.
     */
    public function show(Timesheet $timesheet): JsonResponse
    {
        $entries = $timesheet->entries()
            ->orderBy('date')
            ->get()
            ->map(function ($entry) {
                return [
                    'id'             => $entry->id,
                    'date'           => $entry->date->toDateString(),
                    'day_name'       => $entry->date->translatedFormat('l'),
                    'check_in'       => $entry->check_in?->format('H:i'),
                    'check_out'      => $entry->check_out?->format('H:i'),
                    'break_duration' => $entry->break_duration,
                    'total_hours'    => (float) $entry->total_hours,
                    'planned_hours'  => (float) $entry->planned_hours,
                    'overtime_hours' => (float) $entry->overtime_hours,
                    'deviation'      => $entry->deviation,
                    'management_hours' => (float) $entry->management_hours,
                    'on_call_hours'    => (float) $entry->on_call_hours,
                    'training_hours'   => (float) $entry->training_hours,
                    'absence_type'     => $entry->absence_type,
                    'is_absence'       => $entry->is_absence,
                    'comment'          => $entry->comment,
                    // Alertes / anomalies
                    'alerts'         => $this->detectAlerts($entry),
                ];
            });

        return response()->json([
            'timesheet' => [
                'id'            => $timesheet->id,
                'employee_id'   => $timesheet->employee_id,
                'period_start'  => $timesheet->period_start->toDateString(),
                'period_end'    => $timesheet->period_end->toDateString(),
                'status'        => $timesheet->status,
                'total_hours'   => $timesheet->total_hours,
                'planned_hours' => $timesheet->total_planned_hours,
                'overtime'      => $timesheet->total_overtime_hours,
                'deviation'     => $timesheet->hours_deviation,
            ],
            'entries' => $entries,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Contrôles automatiques (détection des anomalies)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Détecter les alertes / anomalies sur une entrée.
     */
    private function detectAlerts(TimesheetEntry $entry): array
    {
        $alerts = [];

        // Vérification de la cohérence des horaires
        if ($entry->check_in && $entry->check_out && $entry->check_out <= $entry->check_in) {
            $alerts[] = [
                'type' => 'error',
                'message' => 'Heure de départ antérieure ou égale à l\'heure d\'arrivée.',
            ];
        }

        // Journée sans heures et sans absence
        if ((float) $entry->total_hours === 0.0 && empty($entry->absence_type) && $entry->check_in === null) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Aucune heure saisie et aucune absence renseignée.',
            ];
        }

        // Écart significatif par rapport au planning (> 2h)
        if ((float) $entry->planned_hours > 0 && abs($entry->deviation) > 2) {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf(
                    'Écart de %.1fh par rapport au planning prévu.',
                    $entry->deviation
                ),
            ];
        }

        // Heures excessives (> 12h)
        if ((float) $entry->total_hours > 12) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Journée de travail supérieure à 12 heures.',
            ];
        }

        // Pause anormalement courte sur une longue journée (> 6h sans pause)
        if ((float) $entry->total_hours > 6 && $entry->break_duration < 20) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Pause inférieure à 20 min pour une journée de plus de 6 heures.',
            ];
        }

        return $alerts;
    }
}
