<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\GalleryItem;
use App\Models\RestaurantItem;

class ResortApiController extends Controller
{
    public function amenities()
    {
        return Amenity::where('is_active', true)->get();
    }

    public function menu()
    {
        return RestaurantItem::with('restaurantCategory')->where('is_available', true)->get();
    }

    public function gallery()
    {
        return GalleryItem::with('galleryCategory')->get();
    }
}
