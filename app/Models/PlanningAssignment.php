<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une "Affectation de Planning".
 * C'est le lien concret entre un employé et un modèle de planning type (semaine type)
 * pour une période donnée (start_date -> end_date).
 */
class PlanningAssignment extends Model
{
    // Attributs autorisés pour l'assignation de masse
    protected $fillable = [
        'planning_model_id', // Lien vers le modèle d'heures (PlanningModel)
        'employee_id',       // Lien vers l'employé (Employee)
        'start_date',        // Date de début d'application de ce planning
        'end_date',          // Date de fin (optionnelle, null = permanent)
        'status',            // Statut : 'en attente', 'validé', 'suspendu'
        'validated_by',      // ID de l'utilisateur ayant validé
        'validated_at'       // Timestamp de la validation
    ];

    // Conversion automatique des types (casting) pour faciliter la manipulation des dates
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'validated_at' => 'datetime',
    ];

    /**
     * Relation : Le modèle d'heures (PlanningModel) associé à cette affectation.
     */
    public function planningModel(): BelongsTo
    {
        return $this->belongsTo(PlanningModel::class);
    }

    /**
     * Relation : L'employé (Employee) à qui ce planning est assigné.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Relation : L'administrateur ou CP (User) ayant validé cette affectation.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}