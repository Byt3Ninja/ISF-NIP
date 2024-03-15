<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'service_id','rate','comment'];
    protected $primaryKey = ['user_id', 'service_id'];

}
