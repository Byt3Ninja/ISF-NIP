<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MentorshipRequest extends Model
{
    protected $table = 'mentorship_requests';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = ['zoom_date'  => 'date:Y-m-d H:i'];

    public function getCodeAttribute(){
        return "M-".$this->mentor->id.'-'.$this->user->id.'-'.$this->id;
    }
    // public function getZoomDateAttribute()
    // {
    //     return date('Y-m-d h:i a',strtotime($this->attributes['zoom_date']));
    // }
    // public function getZoomDateOrgAttribute()
    // {
    //     return date('Y-m-d H:i',strtotime($this->attributes['zoom_date']));
    // }
    // public function getEndZoomDateAttribute()
    // {
    //     return date("Y-m-d h:i a", strtotime('+'.$this->attributes['period'].' minutes', strtotime($this->attributes['zoom_date'])));
    // }
    // public function getEndZoomDateOrgAttribute()
    // {
    //     return date("Y-m-d H:i", strtotime('+'.$this->attributes['period'].' minutes', strtotime($this->attributes['zoom_date'])));
    // }
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function mentor(){
        return $this->belongsTo(User::class, 'mentor_id','id');
    }
    public function admin(){
        return $this->belongsTo(User::class, 'admin_id','id');
    }
    public function mentorshipTasks()
    {
        return $this->hasMany(MentorshipTask::class, 'mentorship_request_id', 'id');

    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

    public function cycle(){
        return $this->belongsTo(Cycle::class, 'cycle_id','id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function orgnization(){
        return $this->belongsTo(Organization::class, 'organization_id','id');
    }

    public function tasks()
 {
    return $this->hasMany(MentorshipTask::class, 'mentorship_request_id');
 }

    public function reports()
    {
        return $this->hasMany(SessionReport::class, 'mentorship_request_id', 'id');
    }

    public function recordings()
    {
        return $this->hasMany(SessionRecording::class, 'mentorship_request_id', 'id');
    }
}
