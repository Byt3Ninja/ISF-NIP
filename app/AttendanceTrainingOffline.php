<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTrainingOffline extends Model
{
    use HasFactory;

    protected $table = 'attendance_training_offline';
    protected $fillable = [
        'user_id',
        'training_session_id',
        'mentorship_request_id',
        'step_id',
        'cycle_id',
        'company_id',
        'attendance',
        'location',
    ];

    // العلاقات بين الجداول
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function mentorshipRequest()
    {
        return $this->belongsTo(MentorshipRequest::class);
    }
}
