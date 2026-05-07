<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant un "Modèle de Planning" (Semaine Type).
 * Ce modèle sert de bibliothèque réutilisable pour définir les horaires hebdomadaires
 * des employés avant leur affectation effective.
 */
class PlanningModel extends Model
{
    // Attributs autorisés pour l'assignation de masse
    protected $fillable = [
        'name',             // Nom du modèle (ex: "35h Standard")
        'description',      // Description facultative de l'usage du planning
        'hours_summary',    // Résumé textuel des horaires (ex: "9h-17h")
        'monday_hours',     // Heures travaillées le lundi (numérique)
        'tuesday_hours',    // Heures travaillées le mardi
        'wednesday_hours',  // Heures travaillées le mercredi
        'thursday_hours',   // Heures travaillées le jeudi
        'friday_hours',    // Heures travaillées le vendredi
        'saturday_hours',   // Heures travaillées le samedi
        'sunday_hours',     // Heures travaillées le dimanche
        'total_hours',      // Somme totale des heures de la semaine (calculée automatiquement)
        'created_by'        // ID de l'administrateur ou du CP ayant créé le modèle
    ];

    /**
     * Relation : L'utilisateur (User) ayant créé ce modèle.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation : Toutes les affectations (PlanningAssignment) qui utilisent ce modèle type.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(PlanningAssignment::class);
    }
}
