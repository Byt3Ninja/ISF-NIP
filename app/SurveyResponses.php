<?php

namespace App;

use App\Like;
use stdClass;
use Carbon\Carbon;
use App\Traits\Auditable;
use Illuminate\Support\Facades\DB;
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

	public static function assign_surveys()
	{
		$user = auth()->user();
		$cycle_id = 0;
		$cycle_idMentor = [];
		$company = $user->company;
		$steps = collect([]);
		$organization_id = null;
		$default_tz = env('APP_TIMEZONE', 'Africa/Cairo');
		$user_tz = $user->timezone ? $user->timezone : $default_tz;
		$now = Carbon::now()->timezone( $user_tz );
		if( $company && $company->cycledata )
		{
			$cycle_id = $company->cycle;
			if( $company->cycledata )
			{
                $organization = $company->cycledata->organization;
                if( !empty( $organization ) ) { $organization_id = $organization->id; }
            }
			$steps = DB::table('steps as s')
				->select([
					's.id',
					's.title',
					's.arr',
					's.stype',
					's.from',
					's.to',
					's.description',
					's.data',
					'c.id AS cycle_id',
					'c.step_type',
				])
				->leftJoin('cycles as c', 'c.id', '=', 's.cycle_id')
				->where('s.cycle_id', $cycle_id)
				->where('s.cycle_id', '!=', 0)
				->whereNotNull('s.cycle_id')
				->orderBy('s.arr', 'asc')
			->get();
		}
		if( $user->hasAnyRole(['mentor']) )
		{
			$cyclementor = $user->mentorCycles->pluck('id')->toArray();
			$cycle_idMentor = implode(',', $cyclementor);
		}

		if( $cycle_id != 0 || $cycle_idMentor != 0 )
		{
			$surveyEligible = [];
			foreach( $steps as $step )
			{
				if( ( optional($step)->stype == 3 || optional($step)->stype == 4 ) && $now->gt( Carbon::parse($step->to)->timezone( $user_tz ) ) )
				{
					$surveyEligible[] = [
						'triggered_on' => $step->stype == 3 ? 'mentorship' : 'training',
						'triggered_at' => $step->id,
						'triggered_by' => 'due'
					];
				}
			}

			if( $user->hasAnyRole(['mentor']) )
			{
				if( empty( $cycle_idMentor ) )
				{
					$my_mentorship_requests = DB::table('mentorship_requests as mr')
						->select([
							'mr.id',
							'mr.period',
							'mr.zoom_date',
							'mr.created_at'
						])
						->whereRaw('1 = 0')
					->get();
				}
				else
				{
					$my_mentorship_requests = DB::table('mentorship_requests as mr')
						->select([
							'mr.id',
							'mr.period',
							'mr.zoom_date',
							'mr.created_at'
						])
						->where('mr.mentor_id', $user->id)
						->where('mr.approved', 'Yes')
						->whereIn('mr.cycle_id', $cyclementor)
					->get();
				}
			}
			else
			{
				$my_mentorship_requests = DB::table('mentorship_requests as mr')
					->select([
						'mr.id',
						'mr.period',
						'mr.zoom_date',
						'mr.created_at'
					])
					->join('attendance_training_offline as ato', function ($join) use ($user) {
						$join->on('ato.mentorship_request_id', '=', 'mr.id')
							->where('ato.user_id', $user->id)
							->where('ato.attendance', 1);
					})
					->where('mr.user_id', $user->id)
					->where('mr.approved', 'Yes')
					->whereNotNull('mr.rating')
					->where('mr.cycle_id', $cycle_id)
				->get();
			}

			$my_mentorship_requests = $my_mentorship_requests->map(function ( $item ) use ( $user_tz ) {
				$item->end_at = ( !empty($item->zoom_date) && !empty($item->period) )
					? Carbon::parse($item->zoom_date)->timezone( $user_tz )->addMinutes($item->period)
					: null;
				$item->added_at = $item->created_at;
				return $item;
			})->filter(function ($item) use ($now) {
				return !empty($item->end_at) && $now->gt($item->end_at);
			})->values();

			if( $user->hasAnyRole(['registered']) )
			{
				$my_trainings = DB::table('training_sessions as ts')
					->select([
						'ts.id',
						'ts.type',
						'ts.duration',
						'ts.datetime',
						'ts.created_at'
					])
					->join('steps as s', 'ts.step_id', '=', 's.id')
					->join('attendance_training_offline as ato', function ($join) use ($user) {
						$join->on('ato.training_session_id', '=', 'ts.id')
							->where('ato.user_id', $user->id)
							->where('ato.attendance', 1);
					})
					->where('s.cycle_id', $cycle_id)
				->get();

				$my_trainings = $my_trainings->map(function ( $item ) use ( $user_tz ) {
					$item->end_at = null;
					$item->added_at = $item->created_at;
					if( $item->type == 'sessions' && !empty($item->datetime) && !empty($item->duration) ) {
						$sessionStart = Carbon::createFromTimestamp($item->datetime)->timezone( $user_tz );
						$item->end_at = $sessionStart->copy()->addMinutes($item->duration);
					} elseif( in_array($item->type, ['online', 'offline']) && !empty($item->datetime) ) {
						$item->end_at = Carbon::createFromTimestamp($item->datetime)->timezone( $user_tz );
					}
					return $item;
				})->filter(function ($item) use ( $now ) {
					return !empty($item->end_at) && $now->gt($item->end_at);
				})->values();

				$my_quizzes = DB::table('quizzes as q')
					->select([
						'q.id',
						'q.end',
						'q.attempts',
						'q.passing_criteria AS criteria',
						'qr.id AS attempt_id',
						'qr.score AS attempt_score'
					])
					->leftJoin('quiz_results as qr', function ($join) use ($user) {
						$join->on('qr.quiz_id', '=', 'q.id')
							->where('qr.user_id', $user->id);
					})
					->where('q.status', 'published')
					->when($organization_id != null, function ($query) use ($organization_id) {
						$query->where('q.organization_id', $organization_id);
					})
				->get();

				$my_quizzes = $my_quizzes->groupBy('id')->map(function ( $items ) use ( $user_tz ) {
					$item = $items->first();
					$quiz = new stdClass;
					$quiz->id = $item->id;
					$quiz->criteria = $item->criteria;
					$quiz->end = Carbon::parse($item->end)->timezone( $user_tz );
					$quiz->trials = $item->attempts;
					$quiz->attempts = $items->map(function ($result) {
						$attempt = new stdClass;
						$attempt->id = $result->attempt_id;
						$attempt->score = $result->attempt_score;
						return $attempt;
					});
					return $quiz;
				})->values()->filter(function ($quiz) use ( $now ) {
					$attempts = $quiz->attempts;
					if( $attempts->count() > 0 )
					{
						if (
							$attempts->where('score', '>=', $quiz->criteria)->count() >= 1
							|| $attempts->where('score', '<', $quiz->criteria)->count() >= $quiz->trials
							|| $now->gt($quiz->end)
						) {
							return true;
						} else {
							return false;
						}
					} else {
						return false;
					}
				})->values();
			}

			foreach( $my_mentorship_requests as $mentorship )
			{
				$surveyEligible[] = [
					'triggered_on' => 'mentorship',
					'triggered_at' => $mentorship->id,
					'triggered_by' => 'session',
					'added_at' => $mentorship->added_at,
					'end_at' => $mentorship->end_at
				];
			}

			if( $user->hasAnyRole(['registered']) )
			{
				foreach( $my_trainings as $session )
				{
					$surveyEligible[] = [
						'triggered_on' => 'training',
						'triggered_at' => $session->id,
						'triggered_by' => 'session',
						'added_at' => $session->added_at,
						'end_at' => $session->end_at
					];
				}

				foreach( $my_quizzes as $quiz )
				{
					$surveyEligible[] = [
						'triggered_on' => 'training',
						'triggered_at' => $quiz->id,
						'triggered_by' => 'quiz'
					];
				}
			}

			$surveysToAdd = [];
			if( $user->hasAnyRole(['mentor']) )
			{
				$surveys = SurveyScopes::whereIn('cycle_id', $user->mentorCycles->pluck('id'))
					->where(function($query) { $query->whereIn('type', ['mentor', 'both'])->orWhereNull('type'); })
					->with(['myResponses'])
				->get()->unique('survey_id');
			}
			else
			{
				$surveys = SurveyScopes::where('cycle_id', $cycle_id)
					->where(function($query) { $query->whereIn('type', ['startup', 'both'])->orWhereNull('type'); })
					->with(['myResponses'])
				->get()->unique('survey_id');
			}

			// Query responses by survey_id (not scope_id) to prevent duplicates across cycles
			$responsesBySurvey = self::where('user_id', $user->id)
				->whereIn('survey_id', $surveys->pluck('survey_id')->unique())
			->get()->groupBy('survey_id');

			foreach( $surveys as $survey_scope )
			{
				$my_responses = $responsesBySurvey->get($survey_scope->survey_id, collect());

				// Initialize fresh collection for each survey to avoid overwriting issues
				$mentorshipFiltered = collect($surveyEligible)->where('triggered_on', 'mentorship');
				$trainingFiltered = collect($surveyEligible)->where('triggered_on', 'training');
					
				if( $survey_scope->scope == 'mentorship' && $mentorshipFiltered->isNotEmpty() )
				{
					$due = $survey_scope->trigger_by_due;
					$session = $survey_scope->trigger_by_session;
					$triggers = [];
					if( $due == true ) { $triggers[] = 'due'; }
					if( $session == true ) { $triggers[] = 'session'; }
					$triggers = array_unique($triggers);
					if( count( $triggers ) == 0 ) {
						$mentorshipFiltered = $mentorshipFiltered->where('triggered_at', null);
					} else {
						$mentorshipFiltered = $mentorshipFiltered->whereIn('triggered_by', $triggers);
					}

					// Filter sessions: must be added after survey creation AND session must have ended
					$surveyCreatedAt = $survey_scope->created_at;
					$mentorshipFiltered = $mentorshipFiltered->filter(function ($item) use ($surveyCreatedAt, $now, $user_tz) {
						// Session must be added to system after survey scope creation
						if( !empty($item['added_at']) && Carbon::parse($item['added_at'])->timezone( $user_tz )->lte( Carbon::parse($surveyCreatedAt)->timezone( $user_tz ) ) ) { return false; }
						// Session must have ended
						if( !empty($item['end_at']) && $now->lte( $item['end_at'] ) ) { return false; }
						return true;
					})->values();

					foreach( $mentorshipFiltered as $conditions )
					{
						$checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
						if( empty( $checkResponse ) )
						{
							$surveysToAdd[] = [
								'user_id' => $user->id,
								'survey_id' => $survey_scope->survey_id,
								'cycle_id' => $survey_scope->cycle_id,
								'scope_id' => $survey_scope->id,
								'triggered_on' => $conditions['triggered_on'],
								'triggered_at' => $conditions['triggered_at'],
								'triggered_by' => $conditions['triggered_by'],
								'responses' => Null
							];
						}
					}
				}
				elseif( $survey_scope->scope == 'training' && $trainingFiltered->isNotEmpty() )
				{
					$due = $survey_scope->trigger_by_due;
					$quiz = $survey_scope->trigger_by_quiz;
					$session = $survey_scope->trigger_by_session;
					$triggers = [];
					if( $due == true ) { $triggers[] = 'due'; }
					if( $quiz == true ) { $triggers[] = 'quiz'; }
					if( $session == true ) { $triggers[] = 'session'; }
					$triggers = array_unique($triggers);
					if( count( $triggers ) == 0 ) {
						$trainingFiltered = $trainingFiltered->where('triggered_at', null);
					} else {
						$trainingFiltered = $trainingFiltered->whereIn('triggered_by', $triggers);
					}
					// Filter sessions: must be added after survey creation AND session must have ended
					$surveyCreatedAt = $survey_scope->created_at;
					$trainingFiltered = $trainingFiltered->filter(function ($item) use ($surveyCreatedAt, $now, $user_tz) {
						// Session must be added to system after survey scope creation
						if( !empty($item['added_at']) && Carbon::parse($item['added_at'])->timezone( $user_tz )->lte( Carbon::parse($surveyCreatedAt)->timezone( $user_tz ) ) ) { return false; }
						// Session must have ended
						if( !empty($item['end_at']) && $now->lte( $item['end_at'] ) ) { return false; }
						return true;
					})->values();
					foreach ($trainingFiltered as $conditions)
					{
						$checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
						if( empty( $checkResponse ) )
						{
							$surveysToAdd[] = [
								'user_id' => $user->id,
								'survey_id' => $survey_scope->survey_id,
								'cycle_id' => $survey_scope->cycle_id,
								'scope_id' => $survey_scope->id,
								'triggered_on' => $conditions['triggered_on'],
								'triggered_at' => $conditions['triggered_at'],
								'triggered_by' => $conditions['triggered_by'],
								'responses' => Null
							];
						}
					}
				}
			}
				
			if( !empty( $surveysToAdd ) )
			{
				$surveysToAdd = collect($surveysToAdd)->unique(function ($item) {
					return $item['user_id'].'-'.$item['survey_id'].'-'.$item['triggered_on'].'-'.$item['triggered_at'].'-'.$item['triggered_by'];
				})->values()->toArray();

				self::insert($surveysToAdd);
			}
		}
	}
}
