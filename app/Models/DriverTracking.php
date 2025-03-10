<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTracking extends Model
{
    /** @use HasFactory<\Database\Factories\DriverTrackingFactory> */
    use HasFactory;

    protected $table = 'driver_tracking';

    protected $primaryKey = 'id';

    protected $fillable = [
        'driver_id',
        'order_id',
        'latitude',
        'longitude',
        'status',
        'address_id'
    ];

    public function driver(){
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function final_location(){
        return $this->belongsTo(Address::class, 'address_id');
    }


}
