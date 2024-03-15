<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use Auditable;
    protected $fillable = ['organization_id', 'img_url','url','order', 'en_title', 'ar_title','user_added'];
}
