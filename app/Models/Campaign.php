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

    public function employees()
    {
        return $this->belongsToMany(Employee::class)
            ->withPivot('assigned_at', 'unassigned_at')
            ->withTimestamps();
    }
}
