<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class SurveyScopes extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'survey_scopes';
	public static $module = 'Survey Scopes';
	protected $primaryKey = 'id';

	protected $fillable = [
		'survey_id',
		'cycle_id',
		'organization_id',
		'scope',
		'trigger_by_session',
		'trigger_by_due',
		'trigger_by_quiz',
	];

	protected $casts = [
		'survey_id' => 'integer',
		'cycle_id' => 'integer',
		'organization_id' => 'integer',
		'scope' => 'string',
		'trigger_by_session' => 'boolean',
		'trigger_by_due' => 'boolean',
		'trigger_by_quiz' => 'boolean',
	];

	public function survey()
	{
		return $this->belongsTo(Surveys::class, 'survey_id');
	}

	public function cycle()
	{
		return $this->belongsTo(Cycle::class, 'cycle_id');
	}

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}

	public function questions()
	{
		return $this->survey->questions();
	}

	public function responses()
	{
		return $this->hasMany(SurveyResponses::class, 'scope_id');
	}

	public function myResponses()
	{
		return $this->responses()->where('user_id', Auth::id());
	}
}
