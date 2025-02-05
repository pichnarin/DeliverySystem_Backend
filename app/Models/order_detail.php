<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_detail extends Model
{
    /** @use HasFactory<\Database\Factories\OrderDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'food_id',
        'quantity',
        'price'
    ];
    
    public function orders(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function food(){
        return $this->belongsTo(Food::class, 'food_id');
    }
}
