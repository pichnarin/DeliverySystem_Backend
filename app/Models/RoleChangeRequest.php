<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleChangeRequest extends Model
{
    protected $fillable = ['user_id', 'requested_role', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


