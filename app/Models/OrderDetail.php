<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    /** @use HasFactory<\Database\Factories\OrderDetailFactory> */
    use HasFactory;

    protected $table = 'order_details';

    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'order_id',
        'food_id',
        'quantity',
        'price',
        'sub_total',
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function food(){
        return $this->belongsTo(Food::class, 'food_id');
    }
}
