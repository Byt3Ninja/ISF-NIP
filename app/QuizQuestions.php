<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QuizQuestions extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'quiz_questions';
	protected $primaryKey = 'id';

	protected $fillable = [
		'quiz_id',
		'question_id',
		'row_no',
	];

	protected $casts = [
		'quiz_id' => 'integer',
		'question_id' => 'integer',
		'row_no' => 'integer',
	];

	public function quiz()
	{
		return $this->belongsTo(Quizzes::class, 'quiz_id');
	}

	public function question()
	{
		return $this->belongsTo(QuestionsBank::class, 'question_id');
	}
}
