<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimesheetHistory extends Model
{
    // La table utilise un nom non standard (pas "timesheet_histories")
    protected $table = 'timesheet_historys';

    // Un historique ne se modifie pas : pas de updated_at
    public $timestamps = false;

    protected $fillable = [
        'timesheet_id',
        'employee_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ──────────────────────────────────────
    // Relations
    // ──────────────────────────────────────

    /**
     * Feuille de temps concernée.
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    /**
     * Employé propriétaire de la feuille de temps.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Auteur du changement de statut (superviseur ou CP).
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'changed_by');
    }
}
