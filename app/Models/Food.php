<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    /** @use HasFactory<\Database\Factories\FoodFactory> */
    use HasFactory;

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function order_details(){
        return $this->hasMany(OrderDetail::class);
    }
}
