<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Press extends Model
{
    use Auditable;
    public static $module = 'Press';
    protected $table = 'press';
    protected $fillable = ['organization_id', 'img_url','url','order', 'en_title', 'ar_title','user_added'];
}
