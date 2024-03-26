<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    use Auditable;
    protected $casts = ['start'  => 'date:Y-m-d', 'end' => 'date:Y-m-d',];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function organization(){
        return $this->belongsTo(Organization::class,'organization_id');
    }
}
