<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    protected $fillable = [
        'employee_id',
        'manager_id',
        'campaign_id',
        'report_date',
        'tasks_completed',
        'issues',
        'next_day_plan',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    /**
     * L'employé qui a rédigé le rapport.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Le responsable qui reçoit le rapport.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * La campagne concernée par le rapport.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
