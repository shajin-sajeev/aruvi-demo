<?php

use App\Http\Controllers\Api\ResortApiController;
use Illuminate\Support\Facades\Route;

Route::get('/rooms', [ResortApiController::class, 'rooms']);
Route::get('/amenities', [ResortApiController::class, 'amenities']);
Route::get('/restaurant-menu', [ResortApiController::class, 'menu']);
Route::get('/gallery', [ResortApiController::class, 'gallery']);
Route::get('/availability', [ResortApiController::class, 'availability']);
