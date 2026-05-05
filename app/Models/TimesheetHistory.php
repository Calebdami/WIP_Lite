<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetHistory extends Model
{
    public $timestamps = false; // important

    protected $fillable = [
        'timesheet_id',
        'employee_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relations

    public function timesheet()
    // Relation vers la feuille de temps à laquelle cet historique appartient
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function employee()
    // Relation vers l'employé qui possède la feuille de temps
    {
        return $this->belongsTo(Employee::class);
    }

    public function author()
    // Relation vers l'employé qui a effectué le changement
    {
        return $this->belongsTo(Employee::class, 'changed_by');
    }
}
