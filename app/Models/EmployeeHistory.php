<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHistory extends Model
{
    // On désactive updated_at car l'historique est immuable
    public $timestamps = false;
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    protected $fillable = [
        'employee_id', 'old_position_id', 'new_position_id',
        'old_status', 'new_status', 'changed_by', 'reason'
    ];

    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class);
    }

    public function oldPosition(): BelongsTo {
        return $this->belongsTo(Position::class, 'old_position_id');
    }

    public function newPosition(): BelongsTo {
        return $this->belongsTo(Position::class, 'new_position_id');
    }

    public function modifier(): BelongsTo {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
