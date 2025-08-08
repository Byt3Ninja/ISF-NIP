<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Finalscreening extends Model
{
    // use Auditable;
    public static $module = 'Final Screening';
    protected $fillable = [ 'company_id','step_id','judges','datetime','duration','zoom_id', 'zoom_passcode','start_url','join_url'];
    protected $casts = ['judges' => 'array'];

    // ==========> Attributes <=====================
    public function getStartDateAttribute()
    {
        return date('Y-m-d h:i a',strtotime($this->attributes['datetime']));
    }
    public function getStartDateOrgAttribute()
    {
        return date('Y-m-d H:i',strtotime($this->attributes['datetime']));
    }
    public function getEndDateAttribute()
    {
        return date("Y-m-d h:i a", strtotime('+'.$this->attributes['duration'].' minutes', strtotime($this->attributes['datetime'])));
    }
    public function getEndDateOrgAttribute()
    {
        return date("Y-m-d H:i", strtotime('+'.$this->attributes['duration'].' minutes', strtotime($this->attributes['datetime'])));
    }

    // =========> Relations <========================
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }
    public function step(){
        return $this->belongsTo(Step::class,'step_id');
    }

    public function evaluationJudgers(){
        return $this->belongsToMany(User::class, 'finalscreenings', 'judges', 'id');

       
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }
}
