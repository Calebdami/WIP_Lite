<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
  //Role associé à plusieurs utilisateurs
    public function users(){
        return $this->hasMany(user::class);
    }
}
