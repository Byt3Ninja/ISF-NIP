<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipTask extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // ==============> Relations
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function mentorshipRequest(){
        return $this->belongsTo(MentorshipRequest::class,'mentorship_request_id');
    }

    public function company(){
        return $this->belongsTo(Company::class,'company_id');
    }

    public function mentor(){
        return $this->belongsTo(User::class,'mentor_id');
    }

    public function comments(){
        return $this->hasMany(MentorshipTaskComment::class,'mentorship_task_id');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'mentorship_task_labels', 'mentorship_tasks_id', 'label_id');
    }

    public function files()
    {
        return $this->hasMany(MentorshipTaskFile::class, 'mentorship_task_id');
    }

    public function logs()
    {
        return $this->hasMany(MentorshipTaskLog::class, 'mentorship_task_id');
    }
}
