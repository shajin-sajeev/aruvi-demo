<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'is_active' => 'boolean'];
}
