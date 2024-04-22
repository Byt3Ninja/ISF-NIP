<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    public $timestamps = false;

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
