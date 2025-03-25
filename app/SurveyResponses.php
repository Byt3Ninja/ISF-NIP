<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SurveyResponses extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'survey_responses';
	public static $module = 'Survey Responses';
	protected $primaryKey = 'id';

	protected $fillable = [
		'user_id',
		'survey_id',
		'cycle_id',
		'scope_id',
		'triggered_on',
		'triggered_at',
		'triggered_by',
		'responses',
	];

	protected $casts = [
		'user_id' => 'integer',
		'survey_id' => 'integer',
		'cycle_id' => 'integer',
		'scope_id' => 'integer',
		'triggered_on' => 'string',
		'triggered_at' => 'string',
		'triggered_by' => 'string',
		'responses' => 'collection',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function survey()
	{
		return $this->belongsTo(Surveys::class, 'survey_id');
	}

	public function cycle()
	{
		return $this->belongsTo(Cycle::class, 'cycle_id');
	}

	public function survey_scope()
	{
		return $this->belongsTo(SurveyScopes::class, 'scope_id');
	}

	public function questions()
	{
		return $this->survey->questions();
	}

	public function mentorship_session()
	{
		return $this->belongsTo(MentorshipRequest::class, 'triggered_at', 'id' );
	}

	public function mentorship_step()
	{
		return $this->belongsTo(Step::class, 'triggered_at', 'id' );
	}

	public function training_session()
	{
		return $this->belongsTo(TrainingSession::class, 'triggered_at', 'id' );
	}

	public function training_step()
	{
		return $this->belongsTo(Step::class, 'triggered_at', 'id' );
	}

	public function training_quiz()
	{
		return $this->belongsTo(Quizzes::class, 'triggered_at', 'id' );
	}
}
