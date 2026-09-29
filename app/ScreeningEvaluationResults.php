<?php

namespace App;

use stdClass;
use Illuminate\Support\Facades\DB;
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

	/**
	 * Company ids that belong to a step's evaluation regardless of current step:
	 * the union of panel-assigned (screenings.users, stype=2), final-assigned
	 * (finalscreenings.company_id, stype=5) and any startup with a stored result
	 * for the step's evaluation. Uses $preloaded collections when provided to
	 * avoid extra queries, otherwise queries directly.
	 */
	protected static function stepCompanyUniverse( $step_id, $getStep, $preloaded = null )
	{
		$ids = collect();

		if( $getStep->stype == 5 )
		{
			$finals = $preloaded
				? $preloaded['finalscreenings']->where('step_id', $step_id)
				: Finalscreening::where('step_id', $step_id)->get();
			$ids = $ids->merge( $finals->pluck('company_id') );
		}
		else
		{
			$screenings = $preloaded
				? $preloaded['screenings']->where('step_id', $step_id)
				: Screening::where('step_id', $step_id)->get();
			$ids = $ids->merge(
				$screenings->pluck('users')->map(function( $u ) {
					return is_array( $u ) ? $u : ( json_decode( $u, true ) ?: [] );
				})->flatten()
			);

			// Startups currently sitting AT this screening step but never assigned
			// to any panel and never evaluated. Without this they are absent from
			// the report's universe entirely and can never be counted "Not
			// Assigned" (GV-2177). Once a startup advances its companies.step moves
			// on, so it stops being "at" this step and is not re-added here — it
			// stays visible in this step only through its stored results above.
			$atStep = $preloaded
				? $preloaded['companies']->where('cycle', $getStep->cycle_id)->where('step', $step_id)
				: Company::where('cycle', $getStep->cycle_id)->where('step', $step_id)->get(['id']);
			$ids = $ids->merge( $atStep->pluck('id') );
		}

		// Startups that have a stored result for this step's evaluation.
		$evaluation = $preloaded
			? ( $preloaded['evaluations']->firstWhere('step', $getStep->id) ?? null )
			: ScreeningEvaluation::getEvaluationData( $getStep->id );
		if( $evaluation && ($evaluation->id ?? null) )
		{
			$results = $preloaded
				? $preloaded['results']->where('evaluation_id', $evaluation->id)
				: self::where('evaluation_id', $evaluation->id)->get(['company_id']);
			$ids = $ids->merge( $results->pluck('company_id') );
		}

		return $ids->map(fn($i) => intval($i))->filter()->unique()->values();
	}

	/**
	 * @param bool $allCompanies When false (default) the company set is limited
	 *   to startups currently AT the step (companies.step = step_id) — used by the
	 *   screening/final assignment pages. When true the company set is widened to
	 *   every startup that belongs to the step's evaluation regardless of its
	 *   current step: the union of panel-assigned (screenings.users),
	 *   final-assigned (finalscreenings.company_id) and any startup with a stored
	 *   result for the step's evaluation. Used by the results pages and reports so
	 *   results stay visible after a startup is advanced to the next step.
	 */
	public static function metadata( $step_id, $company_id = null, $preloaded = null, $allCompanies = false )
	{
		$_steps = collect([]);
		$companies = collect([]);
		// Null (not an empty collection): when the step is missing/invalid the
		// code below still runs and reads $evaluation?->id. An empty collection
		// has no ->id, so $evaluation?->id threw "Property [id] does not exist on
		// this collection instance" (e.g. opening a company profile that resolves
		// to step_id 0). null short-circuits the null-safe accessor cleanly.
		$evaluation = null;
		$results = collect([]);
		$comments = collect([]);
		$get_comments = null;

		$getStep = $preloaded
			? $preloaded['steps']->get($step_id)
			: Step::find( $step_id );

		if( $getStep )
		{
			if( $allCompanies )
			{
				// Union of every startup that belongs to this step's evaluation,
				// regardless of where it currently sits. Built from the same
				// assignment + result data this method already loads below.
				$assignedCompanyIds = self::stepCompanyUniverse( $step_id, $getStep, $preloaded );

				$companies = $preloaded
					? $preloaded['companies']->where('cycle', $getStep->cycle_id)->whereIn('id', $assignedCompanyIds)->values()
					: Company::with(['users', 'stepdata.cycle'])->where('cycle', $getStep->cycle_id)->whereIn('id', $assignedCompanyIds)->get();
			}
			else
			{
				$companies = $preloaded
					? $preloaded['companies']->where('cycle', $getStep->cycle_id)->where('step', $step_id)->values()
					: Company::with(['users', 'stepdata.cycle'])->where('cycle', $getStep->cycle_id)->where('step', $step_id)->get();
			}

			// Derive the cycle list from the step itself (not the company set) so
			// $_steps stays correct even when the widened company set is empty.
			$cycleIds = $companies->pluck('cycle')->push($getStep->cycle_id)->filter()->unique();
			$_steps = $preloaded
				? $preloaded['allSteps']->whereIn('cycle_id', $cycleIds)->sortBy('arr')->values()
				: Step::whereIn('cycle_id', $cycleIds)->orderBy('arr')->get();

			$evaluation = $preloaded
				? ($preloaded['evaluations']->firstWhere('step', $getStep->id) ?? null)
				: ScreeningEvaluation::getEvaluationData( $getStep->id );

			$results = $preloaded
				? $preloaded['results']->where('evaluation_id', $evaluation?->id)->values()
				: self::with(['criteria', 'user'])->where('evaluation_id', $evaluation?->id )->get();

			if( $preloaded )
			{
				$get_comments = $preloaded['comments']->where('evaluation_id', $evaluation?->id)->first();
			}
			else
			{
				$get_comments = ScreeningEvaluationCustomFields::where('name', 'final_comment')->where('evaluation_id', $evaluation?->id)->with(['values'])->first();
			}

			if( !empty( $get_comments ) && !is_null( $get_comments ) )
			{
				$comments = $get_comments->values;
				if( empty( $comments ) || is_null( $comments ) ) { $comments = collect([]); }
			}
		}
		$comments = $comments->map(function($comment) use ($get_comments) {
			// Use direct property access instead of ->where() which triggers a query per comment
			$comment->evaluation_id = ($get_comments && $get_comments->id == $comment->custom_field_id)
				? $get_comments->evaluation_id
				: null;
			return $comment;
		});

		$l = app()->getLocale();
		if( $getStep?->stype == 5 )
		{
			$finals = ($preloaded
				? $preloaded['finalscreenings']->where('step_id', $step_id)->values()
				: Finalscreening::where('step_id', $step_id)->with(['company', 'company.users'])->get()
			)->map(function( $f ) {
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

			$judges = ($preloaded
				? $preloaded['allUsers']->whereIn('id', $users_ids)->values()
				: User::whereIn('id', $users_ids)->get()
			)->map(function( $user ) use ( $l ) {
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
			$getUsers = $preloaded
				? $preloaded['screenings']->where('step_id', $step_id)->values()
				: Screening::where('step_id', $step_id)->with(['user'])->get();

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
		elseif( $getStep?->data == 'grouptogroup' )
		{
			// Convert user IDs to company IDs (same conversion as stype=5 above)
			foreach( $judges as $index => $judge )
			{
				$startup_ids = $judge->startups->toArray();
				$get_companies = $companies->filter(function( $c ) use ( $startup_ids ) {
					return $c->users->whereIn('id', $startup_ids)->count() > 0;
				});
				$judges[$index]->startups = $get_companies->pluck('id');
			}
		}
		else
		{
			// Panel-based screening (addJudgingPanels) stores assigned company IDs in screenings.users.
			// If any panel assignment exists for this step, scope each judge to only their assigned companies.
			// Otherwise fall back to legacy behavior where every judge evaluates every company.
			$hasPanelAssignments = $getUsers->contains(function( $s ) {
				$u = $s->users;
				if( is_string( $u ) ) { $u = json_decode( $u, true ); }
				return is_array( $u ) && count( $u ) > 0;
			});

			if( $hasPanelAssignments )
			{
				foreach( $judges as $index => $judge )
				{
					$assignedCompanyIds = $getUsers->where('user_id', $judge->id)->flatMap(function( $s ) {
						$u = $s->users;
						if( is_string( $u ) ) { $u = json_decode( $u, true ); }
						return is_array( $u ) ? $u : [];
					})->map(fn($id) => intval($id))->unique()->values();

					$judges[$index]->startups = $companies->whereIn('id', $assignedCompanyIds->toArray())->pluck('id')->values();
				}
			}
			else
			{
				foreach( $judges as $index => $judge ) { $judges[$index]->startups = $companies->pluck('id'); }
			}
		}

		// Union each judge's result-bearing companies into their startup set.
		// $judge->startups above is built purely from the assignment source
		// (panel screenings.users / finalscreenings / group). A judge can hold
		// stored results for a company that is NOT in that assignment list — e.g.
		// the startup advanced past the step, or the panel row was edited after
		// evaluation. Without this union those evaluated pairs are dropped from the
		// per-judge company list, so the report CARD shows "0 judges" even though
		// results() (and the modal) resolve the score. Mirrors stepCompanyUniverse(),
		// which already unions result-bearing companies into the company set. Scope
		// to $companies so we never attach a company outside this metadata set.
		$companyIdSet = $companies->pluck('id')->map(fn($i) => intval($i))->unique();
		foreach( $judges as $index => $judge )
		{
			$resultCompanyIds = $results
				->where('user_id', $judge->id)
				->pluck('company_id')
				->map(fn($i) => intval($i))
				->unique()
				->filter(fn($i) => $companyIdSet->contains($i));
			$judges[$index]->startups = collect( $judge->startups )
				->map(fn($i) => intval($i))
				->merge( $resultCompanyIds )
				->unique()
				->values();
		}

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
									// Judges score each sub-criterion on a 1–10 scale (see the
									// score select in _startup_screening_canvas.blade.php), so the
									// raw score is normalized against a max of 10. Dividing by 5
									// here doubled every score (e.g. 76% rendered as 152%); the
									// per-judge breakdown in results() already uses /10 correctly.
									$weighted_score = ( intval( $result->score ) / 10 ) * $weight;
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
			// Stamp the startup's ACTUAL current step (companies.step), not the
			// evaluation step being processed. With $allCompanies=true a startup
			// may be reported under one step's evaluation while currently sitting
			// at another; the step badge / step filter must reflect where it
			// really is. Fall back to the evaluation step for safety when the
			// company carries no step.
			$_companies[$companies_index]->company_step_id = $company->step ?? $getStep->id;
			$_companies[$companies_index]->company_cycle_id = $company->cycle ?? $getStep->cycle_id;
			// Evaluation step this block belongs to (≠ current step once a startup
			// advances). Used by the reports to dedup duplicate rows and to point
			// the "View Complete Evaluation" modal at the step that actually holds
			// the results. Distinct from company_step_id, which tracks current step.
			$_companies[$companies_index]->evaluation_step_id = $getStep->id;
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

		// Load evaluations through getEvaluationData() so each carries its
		// `categories` structure (with weights normalized to fractions). A bare
		// ScreeningEvaluation::get() leaves `categories` unset, which made the
		// weighting block below a no-op and produced a 0% score for every
		// startup on the screening results report. metadata() already loads
		// evaluations this way; this brings the report path in line.
		$evaluationData = ScreeningEvaluation::getEvaluationData($step_ids);
		if (!$evaluationData instanceof \Illuminate\Support\Collection) {
			$evaluationData = collect($evaluationData ? [$evaluationData] : []);
		}
		$evaluations = $evaluationData->keyBy('step');

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
									// 1–10 score scale: normalize against 10, not 5. Matches
									// metadata() and the per-judge breakdown in results().
									$result->weighted_score = (intval($result->score) / 10) * $weight;
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

	public static function results( $step_id, $company_ids = null, $precomputedMetadata = null, $resultsPreloaded = null )
	{
		$company_ids = is_array( $company_ids ) ? $company_ids : ( $company_ids ? [ $company_ids ] : null );
		$l = app()->getLocale();

		$metadata = $precomputedMetadata ?? self::metadata( $step_id );
		$evaluationForm = $metadata['evaluation'];
		$allResults = $metadata['results'];
		$allJudges = $metadata['judges'];

		$targetCompanyIds = $company_ids ?? $metadata['companies']->pluck('id')->toArray();

		// Use preloaded data if available (batched across all steps), otherwise query per step
		$allCustomFields = collect();
		$allFormCommentsByCompany = collect();
		if( count($targetCompanyIds) > 0 && $evaluationForm )
		{
			if( $resultsPreloaded )
			{
				// Filter pre-fetched data for this evaluation
				$allCustomFields = $resultsPreloaded['customFields']
					->whereIn('company_id', $targetCompanyIds)
					->where('evaluation_id', $evaluationForm->id)
					->values();
				$allFormCommentsByCompany = $resultsPreloaded['formComments'];
			}
			else
			{
				$placeholders = implode(',', array_fill(0, count($targetCompanyIds), '?'));
				$allCustomFields = collect(\DB::select("SELECT
						cfv.id AS id,
						cf.name AS field,
						cfv.value AS value,
						cfv.is_approved AS is_approved,
						cfv.user_id AS judger_id,
						cfv.company_id AS company_id,
						cf.evaluation_id AS evaluation_id,
						u.first_name_en AS judger_first_name_en,
						u.last_name_en AS judger_last_name_en,
						u.first_name_ar AS judger_first_name_ar,
						u.last_name_ar AS judger_last_name_ar
					FROM
						screening_evaluation_fields_values cfv
					LEFT JOIN
						screening_evaluation_custom_fields cf ON cf.id = cfv.custom_field_id
					LEFT JOIN
						users u ON u.id = cfv.user_id
					WHERE
						cfv.company_id IN ($placeholders)
						AND cf.evaluation_id = ?
				", array_merge($targetCompanyIds, [$evaluationForm->id])));

				// Batch fetch form_data and comments
				$allFormDataRows = \DB::table('form_data')->whereIn('company_id', $targetCompanyIds)->get();
				if( $allFormDataRows->isNotEmpty() )
				{
					$allFormDataIds = $allFormDataRows->pluck('id')->toArray();
					$allRawComments = \DB::table('form_data_judges_comments AS fc')
						->join('form_data AS fd', 'fd.id', '=', 'fc.form_data_id')
						->join('users AS u', 'u.id', '=', 'fc.user_id')
						->whereIn('fc.form_data_id', $allFormDataIds)
						->select(['fc.user_id', 'fd.question', 'fc.comment', 'fd.company_id'])
						->get();
					$allFormCommentsByCompany = $allRawComments->groupBy('company_id');
				}
			}
		}

		$output = [];

		foreach( $targetCompanyIds as $company_id )
		{
			$results = $allResults->where('company_id', $company_id)->values();
			$judges = $allJudges->map(function( $item ) use ( $company_id ) {
				$cloned = clone $item;
				$cloned->companies = $item->companies->where('id', $company_id)->values();
				return $cloned;
			});

			$criteria = $evaluationForm ? $evaluationForm->categories : collect();
			$criteria = $criteria->map(function( $item ) use ( $results ) {
				$clonedItem = clone $item;
				$subs = $clonedItem->subCriteria->map(function( $sub ) use ( $results ) {
					$clonedSub = clone $sub;
					$weight = $clonedSub->weight;
					$scoped_results = $results->where('criteria_id', $clonedSub->id)->values()->map(function( $oneResult ) use ( $weight ) {
						$output = new stdClass;
						$output->id = $oneResult->id;
						$output->judger_id = $oneResult->user_id;
						$output->judger = new stdClass;
						$output->judger->id = $oneResult->user_id;
						$output->judger->name = $oneResult->user->name;
						$output->given_score = intval( $oneResult->score );
						$output->max = 10;
						$score = ( $oneResult->score / $output->max );
						$output->score = $score * 100;
						$output->comment = $oneResult->comment;
						$output->status = $oneResult->status == true ? 'Published' : 'Draft';
						$output->weight = $weight;
						$output->weighted_score = ( $score * $weight ) * 100;
						return $output;
					});
					$clonedSub->results = $scoped_results;
					$clonedSub->total = $clonedSub->results->sum('given_score');
					$clonedSub->max = $clonedSub->results->sum('max');
					$clonedSub->count = $clonedSub->results->count();
					$clonedSub->average = $clonedSub->max > 0 ? ( $clonedSub->total / $clonedSub->max ) : 0;
					$clonedSub->weighted_total = ( round( $clonedSub->average * 100 ) / 100 ) * $clonedSub->weight;
					return $clonedSub;
				});
				$clonedItem->total = $subs->sum('total');
				$clonedItem->max = $subs->sum('max');
				$clonedItem->weighted_total = round( $subs->sum('weighted_total') * 100 ) / 100;
				$clonedItem->value = ( round( $clonedItem->weighted_total * 100 ) / 100 ) * $clonedItem->weight;
				$clonedItem->subCriteria = $subs;
				return $clonedItem;
			});

			$_criteria = $criteria->map(function( $item ) {
				$output = new stdClass;
				$output->id = $item->id;
				$output->name = $item->name;
				$output->weight = $item->weight;
				$output->details = $item->subCriteria->map(function( $sub ) {
					$output = new stdClass;
					$output->id = $sub->id;
					$output->name = $sub->name;
					$output->weight = $sub->weight;
					$output->total = $sub->total;
					$output->max = $sub->max;
					$output->count = $sub->count;
					$output->average = $sub->average;
					$output->value = round( $sub->weighted_total * 100 ) / 100;
					$output->details = $sub->results->map(function( $result ) {
						$output = new stdClass;
						$output->judger = $result->judger;
						$output->given_score = $result->given_score;
						$output->max = $result->max;
						$output->score = $result->score / 100;
						$output->comment = $result->comment;
						$output->status = $result->status;
						$output->weight = $result->weight;
						$output->value = $result->weighted_score / 100;
						return $output;
					});
					return $output;
				});
				$output->score = ( round( $output->details->sum('value') * 100 ) / 100 );
				$output->value = round( $output->score * $output->weight * 100 ) / 100;
				return $output;
			});

			$customFields = $allCustomFields->where('company_id', $company_id)->values()->map(function( $item ) use ( $l ) {
				$output = new stdClass;
				$output->id = $item->id;
				$output->field = $item->field;
				$output->value = $item->value;
				$output->judger_id = $item->judger_id;
				$output->approval = intval( $item->is_approved ) === 1 ? 'approved' : ( $item->is_approved === null || intval( $item->is_approved ) === 2 ? 'pending' : 'disapproved' );
				$output->name = trim( str_replace(['   ', '  '], ' ', $item->{'judger_first_name_' . $l} . ' ' . $item->{'judger_last_name_' . $l}) );
				return $output;
			});

			$processedJudges = $judges->map(function( $item ) use ( $customFields, $criteria ) {
				$judger_id = $item->id;
				$output = new stdClass;
				$output->id = $item->id;
				$output->name = $item->name;
				$output->phone = $item->phone;
				$output->email = $item->email;
				$output->image_url = $item->image_url;
				$companies = $item->companies->map(function( $company ) use ( $customFields, $judger_id, $criteria ) {
					$comment = $customFields->where('judger_id', $judger_id)->where('field', 'final_comment')->first();
					$company->comment = !empty( $comment ) ? $comment->value : '';
					$company->approval = !empty( $comment ) ? $comment->approval : 'pending';
					$details = $criteria->map(function( $item ) use ( $judger_id ) {
						$details = $item->subCriteria->filter(function( $sub ) use ( $judger_id ) {
							$results = $sub->results->where('judger_id', $judger_id)->values()->first();
							return !empty( $results );
						})->values()->map(function( $sub ) use ( $judger_id ) {
							$results = $sub->results->where('judger_id', $judger_id)->values()->first();
							$output = new stdClass;
							$output->id = $sub->id;
							$output->name = $sub->name;
							$output->weight = $sub->weight;
							$output->score = $results->given_score;
							$output->max = $results->max;
							$output->comment = $results->comment;
							$output->status = $results->status;
							$output->value = ( $results->given_score / $results->max ) * $sub->weight;
							return $output;
						});

						$output = new stdClass;
						$output->id = $item->id;
						$output->name = $item->name;
						$output->weight = $item->weight;
						$output->score = round( $details->sum('value') * 100 ) / 100;
						$output->value = ( round( ( $output->score * $output->weight ) * 100 ) / 100 );
						$output->details = $details;
						return $output;
					});
					$output = new stdClass;
					$output->id = $company->id;
					$output->status = $company->status;
					$output->value = ( round( $details->sum('value') * 100 ) / 100 );
					$output->comment = $company->comment;
					$output->approval = $company->approval;
					$output->details = $details;
					return $output;
				})->first();
				$output->evaluation = $companies;
				return $output;
			})->filter(function( $item ) {
				return !empty( $item->evaluation );
			})->values();

			// Use pre-fetched form comments for this company
			$companyFormComments = $allFormCommentsByCompany->get($company_id, collect());
			$formCommentsByJudge = $companyFormComments->groupBy('user_id');

			$processedJudges = $processedJudges->map(function( $judge ) use ( $formCommentsByJudge ) {
				$judge->form_comments = $formCommentsByJudge->get($judge->id, collect())->map(function( $c ) {
					return (object)[
						'question' => $c->question,
						'comment' => $c->comment,
					];
				})->values();
				return $judge;
			});

			if( $processedJudges->isEmpty() )
			{
				$fallbackJudges = $_criteria->flatMap(function( $category ) {
					return $category->details->flatMap(function( $sub ) {
						return $sub->details->pluck('judger');
					});
				})->filter(function( $j ) {
					return !empty( $j ) && !empty( $j->id );
				})->unique('id')->values();

				$processedJudges = $fallbackJudges->map(function( $j ) use ( $formCommentsByJudge, $customFields, $criteria, $company_id ) {
					$judger_id = $j->id ?? 0;
					$out = new stdClass;
					$out->id = $judger_id;
					$out->name = $j->name ?? '';
					$out->phone = $j->phone ?? '';
					$out->email = $j->email ?? '';
					$out->image_url = $j->image_url ?? '';

					$details = $criteria->map(function( $item ) use ( $judger_id ) {
						$subDetails = $item->subCriteria->filter(function( $sub ) use ( $judger_id ) {
							$r = $sub->results->where('judger_id', $judger_id)->values()->first();
							return !empty( $r );
						})->values()->map(function( $sub ) use ( $judger_id ) {
							$r = $sub->results->where('judger_id', $judger_id)->values()->first();
							$o = new stdClass;
							$o->id = $sub->id;
							$o->name = $sub->name;
							$o->weight = $sub->weight;
							$o->score = $r->given_score;
							$o->max = $r->max;
							$o->comment = $r->comment;
							$o->status = $r->status;
							$o->value = ( $r->given_score / $r->max ) * $sub->weight;
							return $o;
						});

						$o = new stdClass;
						$o->id = $item->id;
						$o->name = $item->name;
						$o->weight = $item->weight;
						$o->score = round( $subDetails->sum('value') * 100 ) / 100;
						$o->value = ( round( ( $o->score * $o->weight ) * 100 ) / 100 );
						$o->details = $subDetails;
						return $o;
					});

					$comment = $customFields->where('judger_id', $judger_id)->where('field', 'final_comment')->first();
					$hasResults = $details->contains(function( $d ) { return $d->details->isNotEmpty(); });

					$evaluation = new stdClass;
					$evaluation->id = $company_id;
					$evaluation->status = $hasResults ? 'Published' : 'Not Evaluated';
					$evaluation->value = ( round( $details->sum('value') * 100 ) / 100 );
					$evaluation->comment = !empty( $comment ) ? $comment->value : '';
					$evaluation->approval = !empty( $comment ) ? $comment->approval : 'pending';
					$evaluation->details = $details;
					$out->evaluation = $evaluation;

					$out->form_comments = $formCommentsByJudge->get($judger_id, collect())->map(function( $c ) {
						return (object)[
							'question' => $c->question,
							'comment' => $c->comment,
						];
					})->values();
					return $out;
				});
			}

			$output[$company_id] = [
				'total' => ( ( round( $_criteria->sum('value') * 100 ) ) / 100),
				'criteria' => $_criteria,
				'judges' => $processedJudges
			];
		}

		return $output;
	}
}
