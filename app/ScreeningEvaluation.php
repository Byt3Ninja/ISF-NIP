<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass;

class ScreeningEvaluation extends Model
{
	use HasFactory;

	/**
	 * Max length of an evaluation criterion / sub-criterion name.
	 * Single source of truth: the controller validates against it and the
	 * evaluation-model editor renders its maxlength / client rules from it.
	 */
	public const CRITERION_NAME_MAX_LENGTH = 100;

	protected $table = 'screening_evaluation';
	protected $fillable = ['step_id', 'status', 'screening_guidelines'];
	protected $casts = [
		'step_id' => 'integer',
		'status' => 'boolean',
	];

	public function step()
	{
		return $this->belongsTo(Step::class);
	}

	public function criteria()
	{
		return $this->hasMany(ScreeningEvaluationCriteria::class, 'evaluation_id');
	}

	public static function getEvaluationData( $id )
	{
		$inputs = [];
		if( is_array( $id ) )
		{
			$placeholders = implode(',', array_fill(0, count($id), '?'));
			$conditions = "se.step_id IN ($placeholders)";
			foreach( $id as $value ) { $inputs[] = $value; }
		}
		else
		{
			$inputs[] = $id;
			$conditions = "se.step_id = ?";
		}

		$evaluationForm_q = "SELECT
				se.id AS evaluation_id,
				se.step_id,
				se.status,
				se.screening_guidelines,
				sec.id AS criteria_id,
				sec.title AS criteria_name,
				sec.weight AS criteria_weight,
				sec.parent_id AS criteria_parent_id,
				sec_child.id AS criteria_child_id,
				sec_child.title AS criteria_child_name,
				sec_child.weight AS criteria_child_weight
			FROM
				screening_evaluation se
			LEFT JOIN
				screening_evaluation_criteria sec ON se.id = sec.evaluation_id AND sec.parent_id IS NULL
			LEFT JOIN
				screening_evaluation_criteria sec_child ON sec.id = sec_child.parent_id AND sec_child.evaluation_id IS NULL
			WHERE
				$conditions
		";
		$evaluationForm = collect(DB::select($evaluationForm_q, $inputs));

		// Batch-fetch steps with cycles to avoid N+1 per evaluation
		$_batchStepIds = $evaluationForm->pluck('step_id')->unique()->filter()->toArray();
		$_batchStepsWithCycles = !empty($_batchStepIds) ? Step::with('cycle')->whereIn('id', $_batchStepIds)->get()->keyBy('id') : collect();

		$evaluationForm = $evaluationForm->groupBy('evaluation_id')->map(function ( $e ) use ($_batchStepsWithCycles) {
			$_e = $e->first();
			$evaluation = new stdClass;
			$evaluation->id = $_e->evaluation_id;
			$evaluation->step = $_e->step_id;
			$evaluation->status = $_e->status == 0 ? 'Draft' : 'Published';
			$evaluation->screening_guidelines = $_e->screening_guidelines;
			$categories = $e->whereNull('criteria_parent_id')->groupBy('criteria_id')->map(function ( $criteria ) {
				$c = $criteria->first();
				$category = new stdClass;
				$category->id = $c->criteria_id;
				$category->name = $c->criteria_name;
				$category->weight = $c->criteria_weight / 100;
				$category->subCriteria = $criteria->map( function ( $subCriteria ) {
					$sub_category = new stdClass;
					$sub_category->id = $subCriteria->criteria_child_id;
					$sub_category->name = $subCriteria->criteria_child_name;
					$sub_category->weight = $subCriteria->criteria_child_weight / 100;
					return $sub_category;
				})->filter( function ( $subCategory ) { return !is_null( $subCategory->id ); })->values();
				return $category;
			})->filter(function( $category ) { return !is_null( $category->id ); })->values();
			$evaluation->categories = $categories;

			// Use pre-fetched step (avoids N+1 per evaluation)
			$step = $_batchStepsWithCycles->get($_e->step_id);
			$evaluation->screening_guidelines = $evaluation->screening_guidelines ?: ($step && $step->cycle ? $step->cycle->program_guideline : null);
			// the authorized link for this viewer (null when there is no file or they may not open it)
			$evaluation->screening_guidelines_file = $step && $step->cycle ? $step->cycle->guidelineFileUrl('program_guideline_file') : null;

			return $evaluation;
		})->values();
		if( !is_array( $id ) ) { $evaluationForm = $evaluationForm->first(); }

		return $evaluationForm;
	}

	public static function getMyFinalComment( $evaluation_id, $user_id, $company_id )
	{
		$finalCommentField = ScreeningEvaluationCustomFields::where('name', 'final_comment')->where('evaluation_id', $evaluation_id)->first();
		if( empty( $finalCommentField ) )
		{
			$finalCommentField = new ScreeningEvaluationCustomFields;
			$finalCommentField->name = 'final_comment';
			$finalCommentField->evaluation_id = $evaluation_id;
			$finalCommentField->save();
		}
		$myComment = ScreeningEvaluationFieldsValues::where('custom_field_id', $finalCommentField->id)->where('user_id', $user_id)->where('company_id', $company_id)->first();
		if( empty( $myComment ) )
		{
			$myComment = new ScreeningEvaluationFieldsValues;
			$myComment->custom_field_id = $finalCommentField->id;
			$myComment->user_id = $user_id;
			$myComment->company_id = $company_id;
			$myComment->save();
		}

		return $myComment;
	}

	public static function getMyEvaluationStatus( $evaluations, $notEvaluatedCompanies, $established_evaluations )
	{
		$evaluations = $evaluations->map(function ( $step ) use ( $notEvaluatedCompanies, $established_evaluations ) {
            $is_established = $established_evaluations->where('step_id', $step->id)->first();
            $companies = $step->companies->map(function( $company ) use ( $notEvaluatedCompanies, $is_established, $step ) {
                $evaluation_status = '';
                if( empty( $is_established ) || is_null( $is_established ) ) { $evaluation_status = 'not_started'; }
                else
                {
                    if( $is_established->status == 0 ) { $evaluation_status = 'not_started'; }
                    else
                    {
                        $responds = $notEvaluatedCompanies->where('company_id', $company->id)->where('step_id', $step->id)->values();
                        if( $responds->count() > 0 )
                        {
                            $havePending = $responds->where('status', 0)->count();
                            $evaluation_status = $havePending > 0 ? 'in_progress' : 'evaluated';
                        } else { $evaluation_status = 'not_evaluated'; }
                    }
                }
                $company->evaluation_status = $evaluation_status;
                return $company;
            });
            $step->companies = $companies;
            return $step;
        });
		return $evaluations;
	}

	public function customFields()
    {
        return $this->hasMany(ScreeningEvaluationCustomFields::class, 'evaluation_id');
    }
}
