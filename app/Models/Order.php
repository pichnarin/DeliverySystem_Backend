<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function driver_tracking(){
        return $this->hasOne(DriverTracking::class);
    }

    public function order_details(){
        return $this->hasMany(OrderDetail::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

}
