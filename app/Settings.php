<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Settings extends Model
{
    // use Auditable;
    public static $module = 'App Settings';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_ID', 'PUSHER_APP_CLUSTER', 'BroadCast'];



    


}
