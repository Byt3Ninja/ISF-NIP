<?php

namespace App;

use stdClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluationResults extends Model
{
	use HasFactory;

	protected $table = 'screening_evaluation_results';
	protected $fillable = ['user_id', 'criteria_id', 'score', 'comment'];
	protected $casts = [
		'evaluation_id' => 'integer',
		'user_id' => 'integer',
		'company_id' => 'integer',
		'criteria_id' => 'integer',
		'score' => 'double',
		'comment' => 'string',
		'status' => 'boolean'
	];

	public function evaluation()
	{
		return $this->belongsTo(ScreeningEvaluation::class, 'evaluation_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function criteria()
	{
		return $this->belongsTo(ScreeningEvaluationCriteria::class, 'criteria_id');
	}

	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id');
	}

	public static function metadata( $step_id, $company_id = null )
	{
		$_steps = collect([]);
		$companies = collect([]);
		$evaluation = collect([]);
		$results = collect([]);
		$comments = collect([]);
		$getStep = Step::find( $step_id );
		if( $getStep )
		{
			$companies = Company::where('cycle', $getStep->cycle_id)->where('step', $step_id)->get();
			$_steps = Step::whereIn('cycle_id', $companies->pluck('cycle')->unique())->orderBy('arr')->get();
			$evaluation = ScreeningEvaluation::getEvaluationData( $getStep->id );
			$results = self::where('evaluation_id', $evaluation?->id )->get();
			$get_comments = ScreeningEvaluationCustomFields::where('name', 'final_comment')->where('evaluation_id', $evaluation?->id)->with(['values'])->first();
			if( !empty( $get_comments ) && !is_null( $get_comments ) )
			{
				$comments = $get_comments->values;
				if( empty( $comments ) || is_null( $comments ) ) { $comments = collect([]); }
			}
		}
		$comments = $comments->map(function($comment) use ($get_comments) {
			$evaluation_id = null;
			$chkEvaluation = $get_comments->where('id', $comment->custom_field_id)->first();
			$comment->evaluation_id = $chkEvaluation?->evaluation_id ?? null;
			return $comment;
		});

		$l = app()->getLocale();
		if( $getStep?->stype == 5 )
		{
			$finals = Finalscreening::where('step_id', $step_id)->with(['company', 'company.users'])->get()->map(function( $f ) {
				$judges = $f->judges;
				if( !is_array( $judges ) ) { $judges = json_decode( $f->judges ); }
				$f->judgers_ids = array_map(function( $i ) { return intval( $i ); }, $judges);
				return $f;
			});
			$users_ids = $finals->map(function( $u ) {
				$judges = $u->judges;
				if( !is_array( $judges ) ) { $judges = json_decode( $u->judges ); }
				return array_map(function( $i ) { return intval( $i ); }, $judges);
			})->flatten()->unique()->values();

			$judges = User::whereIn('id', $users_ids)->get()->map(function( $user ) use ( $l ) {
				$judger = new stdClass;
				$judger->id = $user->id ?? 0;
				$judger->name = ($user->{'first_name_' . $l} ?? '') . ' ' . ($user->{'last_name_' . $l} ?? '');
				$judger->phone = $user->phone ?? '';
				$judger->email = $user->email ?? '';
				$judger->image_url = $user->image_url ?? '';
				return $judger;
			})->unique('id')->values();

			$judges = $judges->map(function( $item ) use ( $finals ) {
				$startups = $finals->filter(function( $f ) use ( $item ) {
					return in_array($item->id, $f->judgers_ids);
				})->values()->map(function( $i ) {
					return $i?->company?->users;
				})->flatten()->unique()->filter(function( $i ) {
					return !is_null( $i );
				})->values();
				$item->startups = $startups;
				return $item;
			});
		}
		else
		{
			$getUsers = Screening::where('step_id', $step_id)->with(['user'])->get();
			$judges = $getUsers->map(function($item) use ( $l ) {
				$user = $item->user;
				$judger = new stdClass;
				$judger->id = $user->id ?? 0;
				$judger->name = ($user->{'first_name_' . $l} ?? '') . ' ' . ($user->{'last_name_' . $l} ?? '');
				$judger->phone = $user->phone ?? '';
				$judger->email = $user->email ?? '';
				$judger->image_url = $user->image_url ?? '';
				return $judger;
			})->unique('id')->values();

			$judges = $judges->map(function( $item ) use ( $getUsers ) {
				$startups = $getUsers->where('user_id', $item->id)->map(function( $i ) {
					return $i->users;
				})->flatten()->unique()->filter(function( $i ) {
					return !is_null($i);
				})->values();
				$item->startups = $startups;
				return $item;
			});
		}

		if( $getStep?->stype == 5 )
		{
			foreach( $judges as $index => $judge )
			{
				$startup_ids = $judge->startups->pluck('id')->toArray();
				$get_companies = $companies->filter(function( $c ) use ( $startup_ids ) {
					return $c->users->whereIn('id', $startup_ids)->count() > 0;
				});
				$judges[$index]->startups = $get_companies->pluck('id');
			}
		}
		elseif( $getStep?->data != 'grouptogroup' ) { foreach( $judges as $index => $judge ) { $judges[$index]->startups = $companies->pluck('id'); } }

		$scoped_comments = $comments->where('evaluation_id', $evaluation?->id)->values();
		foreach( $judges as $index => $judge )
		{
			$judges[$index]->companies = collect([]);
			foreach( $judge->startups as $company_id )
			{
				$_comment = '';
				$_approval = 'pending';
				$judge_comment = $scoped_comments->where('user_id', $judge->id)->where('company_id', $company_id)->first();
				if( !empty( $judge_comment ) )
				{
					$_comment = $judge_comment?->value ?? '';
					$_approval = intval( $judge_comment->is_approved ) === 1 ? 'approved' : ( $judge_comment->is_approved === null || intval( $judge_comment->is_approved ) === 2 ? 'pending' : 'disapproved' );
				}
				$_company = new stdClass;
				$_company->id = $company_id;
				$_results = $results->where('company_id', $company_id)->where('user_id', $judge->id)->values();
				if( $_results->count() > 0 )
				{
					$_company->status = $_results->where('status', 0)->count() > 0 ? 'Draft' : 'Published';
				}
				else
				{
					$_company->status = 'Not Evaluated';
				}
				$_company->comment = $_comment;
				$_company->approval = $_approval;
				$_company->results = $_results;
				$judges[$index]->companies->push($_company);
			}
		}
		if( $getStep )
		{
			if( !empty( $evaluation->categories ) )
			{
				foreach( $evaluation->categories as $criteria )	
				{
					$subCriteria = $criteria->subCriteria;
					foreach( $judges as $judges_key => $judge )
					{
						foreach( $judge->companies as $companies_key => $company )
						{
							foreach( $company->results as $results_key => $result )
							{
								if( !isset( $result->weighted_score ) ) { $result->weighted_score = 0; }
								if( !isset( $result->parent_criteria ) ) { $result->parent_criteria = $result->criteria->parent_id; }
								$getResult = $subCriteria->where('id', $result->criteria_id)->first();
								if( !empty( $getResult ) )
								{
									$weight = $getResult->weight;
									$weighted_score = ( intval( $result->score ) / 5 ) * $weight;
									$result->weighted_score = $weighted_score;
								}
								$company->results[$results_key] = $result;
							}
							$judge->companies[$companies_key] = $company;
						}
						$judges[$judges_key] = $judge;
					}
				}
			}
		}
		$_companies = $companies;
		foreach( $_companies as $companies_index => $company )
		{
			foreach( $judges as $judges_key => $judge )
			{
				if( in_array( $company->id, $judge->startups->toArray() ) )
				{
					$judger_evaluation = collect( $judge->companies )->where('id', $company->id)->first();
					$evaluation_status = $judger_evaluation->status;
					$score = $judger_evaluation->results->groupBy('parent_criteria')->sum( function( $grouped ) use ( $evaluation ) {
						$parent_id = $grouped->first()->parent_criteria;
						$groupWeight = $evaluation->categories->where('id', $grouped->first()->parent_criteria)->first()->weight;
						return $groupWeight * $grouped->sum('weighted_score');
					});
					$judger_score = round( ( $score * 100 ) );
					foreach( $judge->companies as $companies_key => $_company )
					{
						if( $company->id == $_company->id )
						{
							$judges[$judges_key]->companies[$companies_key]->score = $judger_score;
							$judges[$judges_key]->companies[$companies_key]->status = $evaluation_status;
						}
					}

					$judger_companies = collect( $judges[$judges_key]->companies );
					$judges[$judges_key]->companies = $judger_companies;
				}
			}

			$average_score = $judges->filter(function($item) use ($company) {
				return collect($item->companies)->where('status', 'Published')->where('id', $company->id)->isNotEmpty();
			})->avg(function($item) use ($company) {
				$companyItem = collect($item->companies)->where('status', 'Published')->where('id', $company->id)->first();
				return $companyItem ? $companyItem->score : null;
			});

			$_companies[$companies_index]->average_score = $average_score ?? 0;
			$_companies[$companies_index]->judges = $judges;
			$_companies[$companies_index]->company_step_id = $getStep->id;
			$_companies[$companies_index]->company_cycle_id = $getStep->cycle_id;
		}

		return [
			'judges' => $judges,
			'_steps' => $_steps,
			'companies' => $companies,
			'_companies' => $_companies,
			'evaluation' => $evaluation,
			'results' => $results,
			'comments' => $comments
		];
	}

	public static function metadataReport( $step_ids )
	{
		$step_ids = is_array($step_ids) ? $step_ids : [$step_ids];

		$steps = Step::whereIn('id', $step_ids)->get()->keyBy('id');
		if ($steps->isEmpty()) {
			return [
				'judges' => collect(),
				'_steps' => collect(),
				'companies' => collect(),
				'_companies' => collect(),
				'evaluation' => collect(),
				'results' => collect(),
				'comments' => collect(),
			];
		}

		$all_cycles = $steps->pluck('cycle_id')->unique()->toArray();
		$all_companies = Company::whereIn('cycle', $all_cycles)->get();
		$all_steps = Step::whereIn('cycle_id', $all_cycles)->orderBy('arr')->get();

		$evaluations = ScreeningEvaluation::whereIn('step_id', $step_ids)->get()->keyBy('step_id');

		$evaluation_ids = $evaluations->pluck('id')->filter()->values();
		$all_results = self::whereIn('evaluation_id', $evaluation_ids)->get();

		$comments_map = [];
		if ($evaluation_ids->isNotEmpty()) {
			$final_comments = ScreeningEvaluationCustomFields::whereIn('evaluation_id', $evaluation_ids)
				->where('name', 'final_comment')
				->with(['values'])
				->get();

			foreach ($final_comments as $comment) {
				$comments_map[$comment->evaluation_id] = $comment->values ?? collect();
			}
		}

		$output = [];

		foreach ($steps as $step_id => $step) {
			$evaluation = $evaluations->get($step_id);
			$results = $all_results->where('evaluation_id', $evaluation?->id ?? null);

			$comments = $comments_map[$evaluation?->id ?? null] ?? collect();

			$companies = $all_companies->where('cycle', $step->cycle_id);

		
			if ($step->stype == 5) {
				$finals = Finalscreening::where('step_id', $step_id)
					->with(['company', 'company.users'])
					->get()
					->map(function ($f) {
						$judges = $f->judges;
						if( !is_array( $judges ) ) { $judges = json_decode( $f->judges ); }
						$f->judgers_ids = array_map('intval', (array) $judges);
						return $f;
					});

				$users_ids = $finals->pluck('judgers_ids')->flatten()->unique()->values();
				$judges = User::whereIn('id', $users_ids)->get();
			} else {
				$screenings = Screening::where('step_id', $step_id)->with('user')->get();
				$judges = $screenings->pluck('user')->unique('id');
				$finals = $screenings; 
			}


			$l = app()->getLocale();
			$judges = $judges->map(function ($user) use ($l) {
				$judger = new \stdClass();
				$judger->id = $user->id ?? 0;
				$judger->name = ($user->{'first_name_' . $l} ?? '') . ' ' . ($user->{'last_name_' . $l} ?? '');
				$judger->phone = $user->phone ?? '';
				$judger->email = $user->email ?? '';
				$judger->image_url = $user->image_url ?? '';
				return $judger;
			})->values();

			foreach ($judges as $judge) {
				if ($step->stype == 5) {
					$startup_ids = $finals
						->filter(fn($f) => in_array($judge->id, $f->judgers_ids))
						->pluck('company.users')
						->flatten()
						->pluck('id')
						->unique()
						->toArray();

					$judge->startups = $companies
						->filter(fn($c) => $c->users->whereIn('id', $startup_ids)->isNotEmpty())
						->pluck('id');
				} elseif ($step->data != 'grouptogroup') {
					$judge->startups = $companies->pluck('id');
				} else {
					$judge->startups = collect();
				}
			}
			foreach ($judges as $judge) {
				$judge->companies = collect();
				foreach ($judge->startups as $cid) {
					$company_results = $results->where('company_id', $cid)->where('user_id', $judge->id);
					$status = $company_results->isEmpty() ? 'Not Evaluated' :
						($company_results->contains('status', 0) ? 'Draft' : 'Published');

					$judge->companies->push((object)[
						'id' => $cid,
						'status' => $status,
						'results' => $company_results->values(),
					]);
				}
			}
			if ($evaluation && !empty($evaluation->categories)) {
				foreach ($evaluation->categories as $criteria) {
					$subCriteria = $criteria->subCriteria;
					foreach ($judges as $judge) {
						foreach ($judge->companies as $company) {
							foreach ($company->results as $result) {
								if (!isset($result->weighted_score)) $result->weighted_score = 0;
								if (!isset($result->parent_criteria)) $result->parent_criteria = $result->criteria->parent_id ?? null;

								$sub = $subCriteria->firstWhere('id', $result->criteria_id);
								if ($sub) {
									$weight = $sub->weight;
									$result->weighted_score = (intval($result->score) / 5) * $weight;
								}
							}
						}
					}
				}
			}
			$_companies = $companies->map(function ($company) use ($judges, $evaluation) {
				$publishedJudges = $judges->filter(function ($judge) use ($company) {
					return $judge->companies->where('id', $company->id)->where('status', 'Published')->isNotEmpty();
				});

				$scores = $publishedJudges->map(function ($judge) use ($company, $evaluation) {
					$comp = $judge->companies->firstWhere('id', $company->id);
					if (!$comp) return null;

					$score = $comp->results->groupBy('parent_criteria')->sum(function ($group) use ($evaluation) {
					$parentId = $group->first()->parent_criteria;


					$groupWeight = 0;
					if ($evaluation && $evaluation->categories) {
						$cat = $evaluation->categories->firstWhere('id', $parentId);
						$groupWeight = $cat?->weight ?? 0;
					}

					return $groupWeight * $group->sum('weighted_score');
				});

					return round($score * 100);
				})->filter();

				$avg = $scores->isEmpty() ? 0 : $scores->avg();
				$company->average_score = $avg;
				return $company;
			});

			foreach ($judges as $judge) {
				foreach ($judge->companies as $company) {
					$fullCompany = $_companies->firstWhere('id', $company->id);
					if ($fullCompany) {
						$company->score = $fullCompany->average_score;
					}
				}
			}

			$output[$step_id] = [
				'judges' => $judges,
				'_steps' => $all_steps->where('cycle_id', $step->cycle_id),
				'companies' => $companies,
				'_companies' => $_companies,
				'evaluation' => $evaluation,
				'results' => $results,
				'comments' => $comments,
			];
		}
		return $output;
	}
}
