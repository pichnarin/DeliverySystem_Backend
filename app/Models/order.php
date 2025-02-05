<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'driver_id',
        'address_id',
        'status',
        'final_latitude',
        'final_longitude',
        'payment_method',
        'amount'
    ];

    public function address(){
        return $this->belongsTo(Address::class, 'address_id');

    }

    public function customer(){
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function driverTracking(){
        return $this->belongsTo(Person::class, 'driver_id');
    }

    public function orderDetails(){
        return $this->hasMany(Order_detail::class);
    }

}
