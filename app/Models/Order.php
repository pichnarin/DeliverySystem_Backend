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
        'notes',
        'estimated_delivery_time',
    ];

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function driver_tracking(){
        return $this->hasOne(DriverTracking::class, 'order_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
    

    public function address(){
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function driver(){
        return $this->belongsTo(User::class, 'driver_id');
    }
}
