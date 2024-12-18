<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestions extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'survey_questions';
	protected $primaryKey = 'id';

	protected $fillable = [
		'survey_id',
		'question_id',
		'row_no',
	];

	protected $casts = [
		'survey_id' => 'integer',
		'question_id' => 'integer',
		'row_no' => 'integer',
	];

	public function survey()
	{
		return $this->belongsTo(Surveys::class, 'survey_id');
	}

	public function question()
	{
		return $this->belongsTo(QuestionsBank::class, 'question_id');
	}
}
