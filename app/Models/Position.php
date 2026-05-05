<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    /**
     * Récupérer tous les employés associés à ce poste
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
