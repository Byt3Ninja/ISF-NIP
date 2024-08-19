<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QuizResponses extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'quiz_responses';
	protected $primaryKey = 'id';

	protected $fillable = [
		'result_id',
		'quiz_id',
		'question_id',
		'user_id',
		'response',
		'status',
	];

	protected $casts = [
		'result_id' => 'integer',
		'quiz_id' => 'integer',
		'question_id' => 'integer',
		'user_id' => 'integer',
		'response' => 'string',
		'status' => 'boolean',
	];

	public function result()
	{
		return $this->belongsTo(QuizResults::class, 'result_id');
	}

	public function quiz()
	{
		return $this->belongsTo(Quizzes::class, 'quiz_id');
	}

	public function question()
	{
		return $this->belongsTo(QuestionsBank::class, 'question_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
