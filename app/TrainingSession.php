<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    // use Auditable;.
    // stype
    // 1: Custom Form
    // 2: Screening Step
    // 3: Mentorship Step
    // 4: Training Step
    // 5: Final screening Step
    protected $table = 'training_sessions';
    public static $module = 'Training Sessions';
    protected $fillable = ['step_id', 'title', 'dsc', 'datetime', 'enddatetime', 'duration', 'zoom_id', 'zoom_passcode', 'start_url', 'join_url', 'trainer_name', 'trainer_email', 'training_Address', 'training_location', 'QrCode', 'user_id', 'type'];
    protected $casts = ['datetime' => 'date:Y-m-d H:i',];


    // ==========> Attributes <============
    // public function getStartFormatAttribute()
    // {
    //     return date('Y-m-d h:i a', strtotime($this->attributes['datetime']));
    // }
    // public function getStartOrgAttribute()
    // {
    //     return date('Y-m-d H:i', strtotime($this->attributes['datetime']));
    // }
    public function getEndFormatAttribute()
    {
        return date("Y-m-d h:i a", strtotime('+' . $this->attributes['duration'] . ' minutes', strtotime($this->attributes['datetime'])));
    }
    public function getEndOrgAttribute()
    {
        return date("Y-m-d H:i", strtotime('+' . $this->attributes['duration'] . ' minutes', strtotime($this->attributes['datetime'])));
    }
    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id');
    }

    public function course()
    {
        return $this->belongsTo(Training::class, 'training_Address');
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

    public function reports()
    {
        return $this->hasMany(SessionReport::class, 'session_id');
    }

    public function recordings()
    {
        return $this->hasMany(SessionRecording::class, 'session_id');
    }
    public function attendance()
    {
        return $this->hasMany(SessionAttendance::class, 'session_id');
    }
}