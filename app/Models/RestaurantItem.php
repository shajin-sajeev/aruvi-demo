<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantItem extends Model
{
    protected $guarded = [];

    protected $casts = ['price' => 'decimal:2', 'is_signature' => 'boolean', 'is_available' => 'boolean'];

    public function restaurantCategory()
    {
        return $this->belongsTo(RestaurantCategory::class);
    }
}
