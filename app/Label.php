<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function trainings()
    {
        return $this->belongsToMany(MentorshipTask::class, 'label_taks','label_id', 'mentorship_tasks_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
