<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramTaskActivity extends Model
{
	use HasFactory;
	protected $fillable = ['program_task_id', 'user_id', 'action', 'column', 'was', 'new', 'company_id'];

	public function user() { return $this->belongsTo(User::class, 'user_id'); }

	public function task() { return $this->belongsTo(ProgramTask::class, 'program_task_id'); }
}
