<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'employee_id',
        'campaign_id',
        'manager_id',
        'position_id',
        'status',
        'start_date',
        'end_date'
    ];

    /**
     * Employé assigné
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Campagne liée
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Manager 
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Position 
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}