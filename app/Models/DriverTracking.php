<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTracking extends Model
{
    /** @use HasFactory<\Database\Factories\DriverTrackingFactory> */
    use HasFactory;

    protected $fillable =[
        'driver_id',
        'order_id',
        'address_id',
        'latitude',
        'longitude'
    ];

    public function driver(){
        return $this->belongsTo(Person::class, 'driver_id');
    }

    public function address(){
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }
}
