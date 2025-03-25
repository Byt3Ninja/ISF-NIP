<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    // use Auditable;
    public static $module = 'Screening';
    protected $fillable = ['step_id','user_id','panel','users'];
    protected $casts = ['users' => 'array'];

    public function step(){
        return $this->belongsTo(Step::class,'step_id');
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

}