<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningHistory extends Model
{
    // Désactiver updated_at car un historique ne se modifie pas
    public $timestamps = false;

    protected $fillable = [
        'planning_assignment_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'created_at'
    ];

    /**
     * L'affectation liée à cet historique.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PlanningAssignment::class, 'planning_assignment_id');
    }

    /**
     * L'utilisateur qui a effectué le changement.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}