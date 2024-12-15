<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'company_id'];

    public function trainings()
    {
        return $this->belongsToMany(MentorshipTask::class, 'mentorship_task_labels','label_id', 'mentorship_tasks_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function mentorshipTasks()
    {
        return $this->belongsToMany(MentorshipTask::class, 'mentorship_task_labels', 'label_id', 'mentorship_tasks_id');
    }
}
