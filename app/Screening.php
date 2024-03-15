<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    use Auditable;
    protected $fillable = ['organization_id', 'step_id','user_id','panel','users'];
    protected $casts = ['users' => 'array',];

}