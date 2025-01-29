<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class food extends Model
{
    /** @use HasFactory<\Database\Factories\FoodFactory> */
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(category::class); //a food belongs to a category
    }

    public function orderDetails()
    {
        return $this->hasMany(order_detail::class); //a food has many order_details
    }
}
