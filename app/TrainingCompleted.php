<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingCompleted extends Model
{
	protected $table = 'training_completed';
	protected $fillable = ['course_id'];

	public function course()
	{
		return $this->belongsTo(Training::class, 'course_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
