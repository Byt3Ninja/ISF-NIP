<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'organization_id',
        'name_en',
        'name_ar',
        'parent_id',
    ];
}
