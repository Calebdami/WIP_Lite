<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanningModel extends Model
{
    // Les champs que l'on peut remplir via PlanningModel::create([...])
    protected $fillable = [
        'name',
        'description',
        'hours_summary',
        'monday_hours',
        'tuesday_hours',
        'wednesday_hours',
        'thursday_hours',
        'friday_hours',
        'saturday_hours',
        'sunday_hours',
        'total_hours',
        'created_by'
    ];

    /**
     * L'utilisateur qui a créé ce modèle de planning.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Liste des affectations liées à ce modèle.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(PlanningAssignment::class);
    }
}
