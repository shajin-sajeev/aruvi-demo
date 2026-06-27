<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $guarded = [];

    protected $casts = ['subscribed_at' => 'datetime', 'is_active' => 'boolean'];
}
