<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'matricule', 'first_name', 'last_name', 
        'birth_date', 'hire_date', 'phone', 'email', 'address', 
        'position_id', 'salary_base', 'status'
    ];

    /**
     * Relation avec l'utilisateur (Compte de connexion)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le poste (Position)
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Relation avec les affectations
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
