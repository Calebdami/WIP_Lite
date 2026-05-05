<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetEntry extends Model
{
    protected $fillable = [
        'timesheet_id',
        'date',
        'check_in',
        'check_out',
        'break_duration',
        'total_hours',
        'planned_hours',
        'overtime_hours',
        'absence_type',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'break_duration' => 'integer',
        'total_hours' => 'decimal:2',
        'planned_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    // Relation

    public function timesheet()
    // Relation vers la feuille de temps à laquelle cette entrée appartient
    {
        return $this->belongsTo(Timesheet::class);
    }
}
