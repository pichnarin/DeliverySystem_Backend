<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory;

    protected $fillable = [
        'profile',
        'role_id',
        'username',
        'email',
        'password',
        'phone',
        'notification_tk',
    ];
    public function role()
    {
        return $this->belongsTo(role::class); //a person belongs to a role
    }

    public function address()
    {
        return $this->hasMany(address::class); //a person has many address
    }

    public function order()
    {
        return $this->hasMany(order::class); //a person has many order
    }
}
