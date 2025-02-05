<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'state',
        'city',
        'street',
        'reference',
        'latitude',
        'longitude'
    ];

    public function customer(){
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function driverTracking(){
        return $this->hasOne(DriverTracking::class);
    }
}
