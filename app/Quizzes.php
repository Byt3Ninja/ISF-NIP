<?php

namespace App;

use App\Casts\PlatformDateTime;
use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Quizzes extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'quizzes';
	protected $primaryKey = 'id';
	public static $module = 'Quizzes';

	protected $fillable = [
		'title',
		'organization_id',
		'training_id',
		'description',
		'start',
		'end',
		'time_limit',
		'attempts',
		'retry_after',
		'passing_criteria',
		'shuffle',
		'status',
	];

	protected $casts = [
		'title' => 'string',
		'organization_id' => 'integer',
		'training_id' => 'integer',
		'description' => 'string',
		// GV-9566: wall-clock in the organization's platform zone, see PlatformDateTime.
		'start' => PlatformDateTime::class . ':,organization_id',
		'end' => PlatformDateTime::class . ':,organization_id',
		'time_limit' => 'double',
		'attempts' => 'integer',
		'retry_after' => 'string',
		'passing_criteria' => 'double',
		'shuffle' => 'boolean',
		'status' => 'string',
	];

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}

	public function training()
	{
		return $this->belongsTo(Training::class, 'training_id');
	}

	public function questions()
	{
		return $this->belongsToMany(QuestionsBank::class, 'quiz_questions', 'quiz_id', 'question_id')->orderBy('row_no');
	}

	public function responses()
	{
		return $this->hasMany(QuizResponses::class, 'quiz_id');
	}

	public function results()
	{
		return $this->hasMany(QuizResults::class, 'quiz_id');
	}

	public function myAttempts()
	{
		return $this->hasMany(QuizResults::class, 'quiz_id')->where('user_id', Auth::id());
	}
}
