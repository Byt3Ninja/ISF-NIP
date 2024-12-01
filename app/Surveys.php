<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Surveys extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'surveys';
	protected $primaryKey = 'id';

	protected $fillable = [
		'title',
		'organization_id',
		'description',
	];

	protected $casts = [
		'title' => 'string',
		'organization_id' => 'integer',
		'description' => 'string',
	];

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}


	public function questions()
	{
		return $this->belongsToMany(QuestionsBank::class, 'survey_questions', 'survey_id', 'question_id')->orderBy('row_no');
	}

	public function responses()
	{
		return $this->hasMany(SurveyResponses::class, 'survey_id');
	}
}
