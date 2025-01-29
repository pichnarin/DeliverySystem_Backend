<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    public function person()
    {
        return $this->belongsTo(person::class); //an order belongs to a person
    }
    public function orderDetails()
    {
        return $this->hasMany(order_detail::class); //an order has many order_details
    }
}
