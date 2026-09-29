<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Screening extends Model
{
    // use Auditable;
    public static $module = 'Screening';
    protected $fillable = ['step_id','user_id','panel','users','session_date','meeting_link','meeting_password','zoom_id','start_url','start_time','end_time','interval'];
    protected $casts = ['users' => 'array'];

    public function step(){
        return $this->belongsTo(Step::class,'step_id');
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
