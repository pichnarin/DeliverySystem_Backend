<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $table = 'addresses';

    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'latitude',
        'longitude',
        'reference',
        'city',
        'state',
        'zip',
        'place_name'
    ];


    public function customer(){
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function driver_tracking(){
        return $this->hasOne(DriverTracking::class, 'address_id');
    }

    public function orders(){
        return $this->hasMany(Order::class, 'address_id');
    }
}
