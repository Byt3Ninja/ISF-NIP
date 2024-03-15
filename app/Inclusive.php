<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inclusive extends Model
{
    protected $fillable = ['organization_id', 'img_url','en_title', 'ar_title','user_added'];
}
