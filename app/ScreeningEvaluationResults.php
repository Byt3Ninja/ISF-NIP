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
		$getStep = Step::find( $step_id );
		if( $getStep )
		{
			$companies = Company::where('cycle', $getStep->cycle_id)->get();
			$_steps = Step::whereIn('cycle_id', $companies->pluck('cycle')->unique())->orderBy('arr')->get();
			$evaluation = ScreeningEvaluation::getEvaluationData( $getStep->id );
			$results = self::where('evaluation_id', $evaluation?->id )->get();
		}

		$l = app()->getLocale();
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

		if( $getStep?->data != 'grouptogroup' ) { foreach( $judges as $index => $judge ) { $judges[$index]->startups = $companies->pluck('id'); } }

		foreach( $judges as $index => $judge )
		{
			$judges[$index]->companies = [];
			foreach( $judge->startups as $company_id )
			{
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
				$_company->results = $_results;
				$judges[$index]->companies[] = $_company;
			}
		}
		if( $getStep )
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
		}

		return [
			'judges' => $judges,
			'_steps' => $_steps,
			'companies' => $companies,
			'_companies' => $_companies,
			'evaluation' => $evaluation,
			'results' => $results
		];
	}
}
