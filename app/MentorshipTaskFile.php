<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipTaskFile extends Model
{
	protected $table = 'mentorship_task_files';
	use HasFactory;
	protected $guarded = ['id', 'created_at', 'updated_at'];

	public function task() { return $this->belongsTo(MentorshipTask::class, 'mentorship_task_id'); }

	public function session() { return $this->belongsTo(MentorshipRequest::class, 'mentorship_request_id'); }

	public function user() { return $this->belongsTo(User::class,'user_id'); }
}
