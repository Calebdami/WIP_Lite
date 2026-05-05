<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'status',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'validated_at' => 'datetime',
    ];

    // Relations

    public function employee()
    // Relation vers l'employé qui possède la feuille de temps
    {
        return $this->belongsTo(Employee::class);
    }

    public function validator()
    // Relation vers l'employé qui a validé la feuille de temps
    {
        return $this->belongsTo(Employee::class, 'validated_by');
    }

    public function entries()
    // Relation vers les entrées de la feuille de temps
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    public function histories()
    // Relation vers l'historique des changements de statut de la feuille de temps
    {
        return $this->hasMany(TimesheetHistory::class);
    }
}
