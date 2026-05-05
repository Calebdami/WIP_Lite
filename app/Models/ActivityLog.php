<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'ip_address'
    ];

    /**
     * Utilisateur qui a fait l'action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}