<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['organization_id', 'user_id','type','days','tfrom','tto','notes','active','session_type','date','dfrom'];
    protected $casts = ['dfrom' => 'date:Y-m-d', 'dto' => 'date:Y-m-d'];
//    protected $casts = ['dfrom' => 'date:Y-m-d', 'dto' => 'date:Y-m-d', 'tfrom'=> 'time:H:i:s', 'tto' => 'time:H:i:s','days'=>'array',];

    public function setSessionTypeAttribute($value)
    {
        $this->attributes['session_type'] = in_array($value, ['Online', 'Offline']) ? $value : 'Both';
    }

}
