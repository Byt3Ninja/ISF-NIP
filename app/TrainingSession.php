<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
//    use Auditable;
    protected $table = 'training_sessions';
    protected $fillable = ['step_id', 'title', 'dsc', 'datetime', 'duration', 'zoom_id', 'start_url', 'join_url','trainer_name','trainer_email','location'];
    protected $casts = ['datetime'  => 'date:Y-m-d H:i',];


    // ==========> Attributes <============
    public function getStartFormatAttribute()
    {
        return date('Y-m-d h:i a',strtotime($this->attributes['datetime']));
    }
    public function getStartOrgAttribute()
    {
        return date('Y-m-d H:i',strtotime($this->attributes['datetime']));
    }
    public function getEndFormatAttribute()
    {
        return date("Y-m-d h:i a", strtotime('+'.$this->attributes['duration'].' minutes', strtotime($this->attributes['datetime'])));
    }
    public function getEndOrgAttribute()
    {
        return date("Y-m-d H:i", strtotime('+'.$this->attributes['duration'].' minutes', strtotime($this->attributes['datetime'])));
    }
    public function step() {
        return $this->belongsTo(Step::class,'step_id');
    }

    public function course(){
        return $this->belongsTo(Training::class,'location');
    }
}
