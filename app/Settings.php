<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Settings extends Model
{
//    use Auditable;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

}
