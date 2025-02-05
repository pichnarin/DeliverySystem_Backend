<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    /** @use HasFactory<\Database\Factories\FoodFactory> */
    use HasFactory;

    protected $fillable =[
        'category_id',
        'image',
        'status',
        'price',
        'name',
        'description'
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function orderDetails(){
        return $this->hasMany(Order_detail::class, 'food_id');
    }
}
