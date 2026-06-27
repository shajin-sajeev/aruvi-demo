<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $guarded = [];

    protected $casts = ['is_published' => 'boolean'];
}
