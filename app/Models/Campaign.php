<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    /**
     * Affectations liées à cette campagne.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Employés travaillant sur cette campagne.
     */
    public function employees()
    {
        return $this->hasManyThrough(Employee::class, Assignment::class, 'campaign_id', 'id', 'id', 'employee_id');
    }
}
