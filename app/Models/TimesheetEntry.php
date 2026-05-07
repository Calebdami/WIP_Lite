<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetEntry extends Model
{
    protected $fillable = [
        'timesheet_id',
        'date',
        'check_in',
        'check_out',
        'break_duration',
        'total_hours',
        'planned_hours',
        'overtime_hours',
        'management_hours',
        'on_call_hours',
        'training_hours',
        'absence_type',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'break_duration' => 'integer',
        'total_hours' => 'decimal:2',
        'planned_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'management_hours' => 'decimal:2',
        'on_call_hours' => 'decimal:2',
        'training_hours' => 'decimal:2',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    /**
     * Feuille de temps parente.
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    // ──────────────────────────────────────
    // Accesseurs calculés
    // ──────────────────────────────────────

    /**
     * Calcule les heures travaillées à partir de check_in, check_out et break_duration.
     * Retourne null si les heures d'arrivée/départ ne sont pas renseignées.
     */
    public function getComputedTotalHoursAttribute(): ?float
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $checkIn = \Carbon\Carbon::parse($this->check_in);
        $checkOut = \Carbon\Carbon::parse($this->check_out);

        // Durée brute en heures
        $rawHours = $checkOut->diffInMinutes($checkIn) / 60;

        // Soustraire la pause (convertie de minutes en heures)
        $breakHours = ($this->break_duration ?? 0) / 60;

        return round(max(0, $rawHours - $breakHours), 2);
    }

    /**
     * Calcule les heures supplémentaires (total_hours - planned_hours si positif).
     */
    public function getComputedOvertimeHoursAttribute(): float
    {
        $diff = (float) $this->total_hours - (float) $this->planned_hours;
        return round(max(0, $diff), 2);
    }

    /**
     * Écart entre heures réelles et prévues (peut être négatif).
     */
    public function getDeviationAttribute(): float
    {
        return round((float) $this->total_hours - (float) $this->planned_hours, 2);
    }

    /**
     * Vérifie si l'employé était absent ce jour.
     */
    public function getIsAbsenceAttribute(): bool
    {
        return !empty($this->absence_type);
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    /**
     * Filtrer les entrées avec des absences.
     */
    public function scopeAbsences($query)
    {
        return $query->whereNotNull('absence_type');
    }

    /**
     * Filtrer les entrées avec des heures supplémentaires.
     */
    public function scopeWithOvertime($query)
    {
        return $query->where('overtime_hours', '>', 0);
    }

    /**
     * Filtrer les entrées avec un écart par rapport au planning.
     */
    public function scopeWithDeviation($query)
    {
        return $query->whereRaw('total_hours != planned_hours');
    }
}
