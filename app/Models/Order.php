<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'id';

    protected $fillable = [
        'order_number',
        'customer_id',
        'driver_id',
        'address_id',
        'status',
        'quantity',
        'total',
        'payment_method',
        'delivery_fee',
        'tax',
        'longitude',
        'latitude',
        'note',
        'estimated_delivery_time',
    ];

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
