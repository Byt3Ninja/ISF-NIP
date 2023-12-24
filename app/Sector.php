<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'parent_id',
    ];
}
