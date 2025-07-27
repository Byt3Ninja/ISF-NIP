<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['organization_id', 'user_id','type','days','tfrom','tto','notes','active'];
    protected $casts = ['dfrom' => 'date:Y-m-d', 'dto' => 'date:Y-m-d','days'=>'array',];
//    protected $casts = ['dfrom' => 'date:Y-m-d', 'dto' => 'date:Y-m-d', 'tfrom'=> 'time:H:i:s', 'tto' => 'time:H:i:s','days'=>'array',];

}
