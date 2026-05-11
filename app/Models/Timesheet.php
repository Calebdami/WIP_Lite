<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'status',
        'validated_by',
        'validated_at',
    ];

    protected $appends = [
        'total_hours',
        'total_planned_hours',
        'total_overtime_hours',
        'hours_deviation',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'validated_at' => 'datetime',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    /**
     * Employé concerné par cette feuille de temps.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Chef de Plateau qui a validé la feuille de temps.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validated_by');
    }

    /**
     * Entrées journalières de la feuille de temps.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    /**
     * Historique des changements de statut.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(TimesheetHistory::class);
    }

    // ──────────────────────────────────────
    // Accesseurs calculés
    // ──────────────────────────────────────

    /**
     * Total des heures travaillées sur la période.
     */
    public function getTotalHoursAttribute(): float
    {
        return (float) $this->entries()->sum('total_hours');
    }

    /**
     * Total des heures prévues sur la période.
     */
    public function getTotalPlannedHoursAttribute(): float
    {
        return (float) $this->entries()->sum('planned_hours');
    }

    /**
     * Total des heures supplémentaires sur la période.
     */
    public function getTotalOvertimeHoursAttribute(): float
    {
        return (float) $this->entries()->sum('overtime_hours');
    }

    /**
     * Écart global entre heures travaillées et heures prévues.
     */
    public function getHoursDeviationAttribute(): float
    {
        return $this->total_hours - $this->total_planned_hours;
    }

    // ──────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────

    /**
     * Filtrer par statut.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filtrer par période contenant une date donnée.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('period_start', '<=', $date)
                     ->where('period_end', '>=', $date);
    }

    /**
     * Feuilles de temps validées uniquement.
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'valide');
    }
}
