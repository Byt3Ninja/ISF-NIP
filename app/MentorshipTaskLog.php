<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipTaskLog extends Model
{
	use HasFactory;
	protected $fillable = ['mentorship_task_id', 'user_id', 'action', 'column', 'was', 'new'];

	public function user() { return $this->belongsTo(User::class, 'user_id'); }
	
	public function task() { return $this->belongsTo(MentorshipTask::class, 'mentorship_task_id'); }
}
