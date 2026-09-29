<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasFileCleanup;

class MentorshipTaskFile extends Model
{
	protected $table = 'mentorship_task_files';
	use HasFactory, HasFileCleanup;
	protected $guarded = ['id', 'created_at', 'updated_at'];
	protected array $fileFields = [
		'file' => 'mentorship_tasks',
	];

	public function getFileUrlAttribute()
	{
		return app(\App\Services\FileManagerService::class)->getUrl($this->file, 'mentorship_tasks');
	}

	public function task() { return $this->belongsTo(MentorshipTask::class, 'mentorship_task_id'); }

	public function session() { return $this->belongsTo(MentorshipRequest::class, 'mentorship_request_id'); }

	public function user() { return $this->belongsTo(User::class,'user_id'); }
}
