<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantCategory extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(RestaurantItem::class)->orderBy('sort_order');
    }
}
