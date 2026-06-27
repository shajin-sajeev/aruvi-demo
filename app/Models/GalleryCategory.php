<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryCategory extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }
}
