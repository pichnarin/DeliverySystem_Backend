<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverTracking extends Model
{
    /** @use HasFactory<\Database\Factories\DriverTrackingFactory> */
    use HasFactory;

    public function driver(){
        return $this->belongsTo(User::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function final_location(){
        return $this->belongsTo(Address::class);
    }


}
