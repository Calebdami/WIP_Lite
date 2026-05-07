<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une entrée dans l'Historique des Plannings.
 * Sert de trace d'audit immuable pour suivre chaque changement de statut
 * d'une affectation de planning (création, validation, suspension).
 */
class PlanningHistorys extends Model
{
    // On précise le nom de la table car il diffère de la convention plurielle par défaut
    protected $table = 'planning_histories';
    
    // On désactive la gestion automatique des timestamps car on gère manuellement created_at 
    // et l'historique n'est jamais mis à jour (pas de updated_at).
    public $timestamps = false;

    // Attributs autorisés pour l'assignation de masse
    protected $fillable = [
        'planning_assignment_id', // Lien vers l'affectation concernée
        'old_status',              // Statut avant le changement (null si création)
        'new_status',              // Nouveau statut appliqué
        'changed_by',              // ID de l'utilisateur (User) auteur du changement
        'reason',                  // Motif ou commentaire sur le changement
        'created_at'               // Date précise de l'évènement
    ];

    /**
     * Relation : L'affectation (PlanningAssignment) faisant l'objet de cet historique.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PlanningAssignment::class, 'planning_assignment_id');
    }

    /**
     * Relation : L'utilisateur (User) qui a déclenché cette modification de statut.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}