<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningAssignment extends Model
{
    protected $fillable = [
        'planning_model_id', 'employee_id', 'start_date', 
        'end_date', 'status', 'validated_by', 'validated_at'
    ];

    // On définit que start_date et end_date sont des dates (Carbon)
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'validated_at' => 'datetime',
    ];

    /**
     * Le modèle de planning utilisé pour cette affectation.
     */
    public function planningModel(): BelongsTo
    {
        return $this->belongsTo(PlanningModel::class);
    }

    /**
     * L'employé concerné par cette affectation.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * L'utilisateur qui a validé l'affectation.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}