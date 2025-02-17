<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function driver_tracking(){
        return $this->hasOne(DriverTracking::class);
    }

    public function order(){
        return $this->hasOne(Order::class);
    }
}
