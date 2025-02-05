<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
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
        'provider',
        'provider_id'
    ];

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(){
        return $this->hasMany(Address::class);
    }

    public function deliveryTracking(){
        return $this->hasOne(DriverTracking::class);
    }
}
