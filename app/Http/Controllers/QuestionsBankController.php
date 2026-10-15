<?php

namespace App\Http\Controllers;

use App\Category;
use App\SubCategory;
use App\Organization;
use App\QuestionsBank;
use App\QuestionCategories;
use App\Stage;
use App\Weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\HtmlSanitizer;
use stdClass;

class QuestionsBankController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index(Request $request)
	{
		$user = auth()->user();
		$organization_id = $user->hasRole('admin') ? null : $user->organizations_ids;
		/* $questions = QuestionsBank::with(['organization']);
		if (!empty($organization_id)) {
			if (is_array($organization_id)) {
				$questions = $questions->whereIn('organization_id', $organization_id);
			} else {
				$questions = $questions->where('organization_id', $organization_id);
			}
		}
		
		if($request->has('search')) {
			$questions = $questions->where('group',$request->search);
		}
		$questions = $questions->get(); */

		$inputs = [];
		$questions_q = "SELECT
				qb.id,
				qb.group,
				qb.question,
				qb.type,
				qb.survey_type,
				qb.tags,
				qb.difficulty,
				org.id AS org_id,
				org.name_ar AS org_name_ar,
				org.name_en AS org_name_en,
				orgs.id AS orgs_id,
				orgs.name_ar AS orgs_name_ar,
				orgs.name_en AS orgs_name_en
			FROM
				questions_bank qb
			LEFT JOIN
				organizations org ON qb.organization_id = org.id
			LEFT JOIN
				organization_question_bank oqb ON qb.id = oqb.question_id
			LEFT JOIN
				organizations orgs ON oqb.organization_id = orgs.id
			WHERE
				qb.id > 0
		";
		if( $request->has('search') && !empty( $request->input('search', null) ) )
		{
			$inputs[] = $request->input('search', null);
			$questions_q .= " AND qb.group = ?";
		}
		if( !empty( $organization_id ) )
		{
			if( is_array( $organization_id ) )
			{
				$invoicesPlaceholders = implode(',', array_fill(0, count( $organization_id ), '?'));
				$questions_q .= " AND ( qb.organization_id IN ( $invoicesPlaceholders ) OR oqb.organization_id IN ( $invoicesPlaceholders ) )";
				$inputs = array_merge( $inputs, $organization_id );
				$inputs = array_merge( $inputs, $organization_id );
			}
			else
			{
				$questions_q .= " AND ( qb.organization_id = ? OR oqb.organization_id = ? )";
				$inputs[] = $organization_id;
				$inputs[] = $organization_id;
			}
		}
		$l = app()->getLocale();
		$questions = collect( DB::select( $questions_q, $inputs ) )->groupBy('id')->map(function( $item ) use ( $l ) {
			$q = $item->first();
			$question = new stdClass;
			$question->id = $q->id;
			$question->group = $q->group;
			$question->question = $q->question;
			$question->type = $q->type;
			$question->survey_type = $q->survey_type;
			$question->tags = json_decode( $q->tags );
			$question->difficulty = $q->difficulty;
			$question->organization = new stdClass;
			$question->organization->id = $q->org_id;
			$question->organization->name = $q->{'org_name_' . $l};
			$question->organizations = $item->filter(function( $q ) {
				return !empty( $q->orgs_id );
			})->map(function( $org ) use ( $l ) {
				$organization = new stdClass;
				$organization->id = $org->orgs_id;
				$organization->name = $org->{'orgs_name_' . $l};
				return $organization;
			})->values();
			return $question;
		})->values();

		$categories = Category::where('category_type', 'Assesment')->get();
		$subcategories = SubCategory::where('Type', 'Assesment')->get();
		return view('admin.questionsBank.index', compact('questions'));
	}

	public function create()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$organizations = Organization::query();
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$organizations = $organizations->whereIn('id', $orgIds);
		}
		$organizations = $organizations->with(['questionsCategories'])->get();

		$categories = Category::where('category_type', 'Assesment')->get();
		$subcategories = SubCategory::where('Type', 'Assesment')->get();

		return view('admin.questionsBank.create', compact('organizations', 'categories', 'subcategories'));
	}

	public function store(Request $request)
	{
		if ($request->question_group != 'assessment') {
			if ($request->has('quiz_answer_status')) {
				$quiz_answer_status = [];
				foreach ($request->quiz_answer_status as $status) {
					$quiz_answer_status[] = intval($status) == 1 || $status == 'true' ? true : false;
				}
				$request->merge(['quiz_answer_status' => $quiz_answer_status]);
			}

			$rules = [
				'organization_id' => 'required|array',
				'category_id' => 'required_if:question_group,quiz|nullable|exists:question_categories,id',
				'difficulty' => 'required_if:question_group,quiz|nullable|in:easy,medium,hard',
				'question' => 'required|string',
				'type' => 'required_if:question_group,quiz|nullable|in:mcq,tfq',
				'question_group' => 'required|in:quiz,survey',
				'survey_type' => 'required_if:question_group,survey|nullable|in:stars,radio,answers',
				'correct' => 'required_if:type,tfq|nullable|in:tfq_true,tfq_false',
				'explanation' => 'nullable|sometimes',
			];
			if ($request->question_group == 'quiz') {
				
				$rules = array_merge($rules, [
					'quiz_answer_text' => 'required_if:type,mcq|array',
					"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
					'quiz_answer_status' => 'required_if:type,mcq|array',
					"quiz_answer_status.*" => "required_if:type,mcq|boolean",
				]);
			}
			if ($request->question_group == 'survey') {

				$text_status = ($request->survey_type != 'stars' && $request->survey_type != 'answers') ?  'required' : 'nullable';
				$rules = array_merge($rules, [
					'quiz_answer_text' => 'array|nullable',
					"quiz_answer_text.*" => "$text_status|string",
				]);
			}
			$validator = Validator::make($request->all(), $rules);
			$errors = [];
			if ($validator->fails()) {
				$errors = $validator->errors()->toArray();
			}
			if (count($errors)) {
				return back()->withInput($request->all())->with('success', false)->withErrors($errors);
			}
			$answers = [];
			$correct = null;
			
			// Process answers for quiz questions (MCQ)
			if ($request->question_group == 'quiz' && $request->type == 'mcq' && 
				$request->has('quiz_answer_text') && $request->has('quiz_answer_status')) {
				foreach ($request->quiz_answer_text as $key => $answer) {
					if (!empty($answer)) {
						$answers[] = ['text' => $answer, 'status' => $request->quiz_answer_status[$key]];
					}
				}
			}
			
			// Process answers for survey questions (radio, answers types)
			if ($request->question_group == 'survey' && $request->survey_type != 'stars' && 
				$request->has('quiz_answer_text')) {
				foreach ($request->quiz_answer_text as $key => $answer) {
					if (!empty($answer)) {
						$answers[] = ['text' => $answer];
					}
				}
			}
			
			// Process TFQ correct answer
			if ($request->question_group == 'quiz' && $request->type == 'tfq') {
				$correct = ($request->has('correct') && $request->correct == 'tfq_true') ? true : false;
			}
			$type = null;
			$surveyType = null;
			$category_id = null;
			
			$question = new QuestionsBank;
			$question->group = $request->question_group;
			if ($request->question_group == 'quiz') {
				$type = $request->type;
				$category_id = $request->category_id;
			} else {
				$surveyType = $request->survey_type;
			}

			$question->category_id = $request->category_id;
			// Strip any JavaScript/XSS vectors; question is rendered raw in quiz views.
			$question->question = HtmlSanitizer::clean($request->question);
			$question->type = $type;
			$question->survey_type = $surveyType;
			$question->correct = $correct;
			$question->answers = $answers;
			$question->explanation = $request->explanation;
			$question->difficulty = $request->difficulty;
			$question->tags = $request->has('tags') && !empty($request->tags) ? explode(',', $request->tags) : [];
			$question->save();
			$question->organizations()->attach($request->organization_id);
		} else {
			$rules = [
				'ascategory_id' => 'required|exists:categories,id',
				'subcategory_id' => 'required|exists:sub_categories,id',
				'question' => 'required|string',
				'question_group' => 'required',
			];

			$validator = Validator::make($request->all(), $rules);
			$errors = [];
			if ($validator->fails()) {
				$errors = $validator->errors()->toArray();
			}
			if (count($errors)) {
				return back()->withInput($request->all())->with('success', false)->withErrors($errors);
			}

			$answers = [];
			$correct = null;
			if (($request->has('quiz_answer_text')) && ($request->has('quiz_answer_score'))) {

				foreach ($request->quiz_answer_text as $key => $answer) {
					if ($key == 0) {
						$score = 1; // للقيمة الأولى
					} elseif ($key == 1) {
						$score = 3; // للقيمة الثانية
					} elseif ($key == 2) {
						$score = 5; // للقيمة الثالثة
					}
					$answers[] = ['text' => $answer, 'suggestion' => $request->quiz_answer_score[$key], 'score' => $score];
				}
			}

			$question = new QuestionsBank;
			$question->group = $request->question_group;
			$question->ascategory_id = $request->ascategory_id;
			$question->subcategory_id = $request->subcategory_id;
			// Strip any JavaScript/XSS vectors; question is rendered raw in quiz views.
			$question->question = HtmlSanitizer::clean($request->question);
			$question->answers = $answers;
			$question->explanation_QA_ar = $request->explanation_QA_ar;
			$question->explanation_QA_en = $request->explanation_QA_en;
			$question->save();
			/* Add Relation Between Question And Organization  from OrgQuestionBank */
			$question->organizations()->attach($request->organization_id);

			$stageIds = Stage::pluck('id');
			$existingStageIds = Weight::where('category_id', $question->ascategory_id)
				->where('subcategory_id', $question->subcategory_id)
				->pluck('stage_id');
			$missingStageIds = $stageIds->diff($existingStageIds);

			if ($missingStageIds->isNotEmpty()) {
				$now = now();
				Weight::insert($missingStageIds->map(fn($stageId) => [
					'stage_id' => $stageId,
					'category_id' => $question->ascategory_id,
					'subcategory_id' => $question->subcategory_id,
					'weight' => 0,
					'created_at' => $now,
					'updated_at' => $now,
				])->values()->all());
			}
		}

		return redirect()->route('questions-bank.index')->with('success', 'StoredSuccessfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$question = QuestionsBank::find($id);
		$selectedOrganizations = $question->organizations->pluck('id')->toArray();
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$organizations = Organization::query();
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$organizations = $organizations->whereIn('id', $orgIds);
		}
		$organizations = $organizations->with(['questionsCategories'])->get();
		$categories = Category::where('category_type', 'Assesment')->get();
		$subcategories = SubCategory::where('Type', 'Assesment')->get();
		return view('admin.questionsBank.edit', compact('organizations', 'question', 'categories', 'subcategories', 'selectedOrganizations'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Type  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$question = QuestionsBank::find($id);
		if (empty($question)) {
			return back()->withInput()->with(['error' => 'questionNotFound']);
		} else {
			if ($request->question_group != 'assessment') {
				if ($request->has('quiz_answer_status')) {
					$quiz_answer_status = [];
					foreach ($request->quiz_answer_status as $status) {
						$quiz_answer_status[] = intval($status) == 1 || $status == 'true' ? true : false;
					}
					$request->merge(['quiz_answer_status' => $quiz_answer_status]);
				}

				$rules = [
					'organization_id' => 'nullable|exists:organizations,id',
					'category_id' => 'required_if:question_group,quiz|exists:question_categories,id',
					'difficulty' => 'required_if:question_group,quiz|in:easy,medium,hard',
					'question' => 'required|string',
					'type' => 'required_if:question_group,quiz|in:mcq,tfq',
					'question_group' => 'required|in:quiz,survey',
					'survey_type' => 'required_if:question_group,survey|in:stars,radio,answers',
					'correct' => 'required_if:type,tfq|in:tfq_true,tfq_false',
					'explanation' => 'nullable|sometimes',
				];
				if ($request->question_group == 'quiz') {
					$rules = array_merge($rules, [
						'quiz_answer_text' => 'required_if:type,mcq|array',
						"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
						'quiz_answer_status' => 'required_if:type,mcq|array',
						"quiz_answer_status.*" => "required_if:type,mcq|boolean",
					]);
				}
				if ($request->question_group == 'survey') {
					$text_status = ($request->survey_type != 'stars') ? 'required' : 'nullable';
					$rules = array_merge($rules, [
						'quiz_answer_text' => 'array|nullable',
						"quiz_answer_text.*" => "$text_status|string",
					]);
				}
				$this->validate($request, $rules);
				$answers = [];
				$correct = null;
				
				// Process answers for quiz questions (MCQ)
				if ($request->question_group == 'quiz' && $request->type == 'mcq' && 
					$request->has('quiz_answer_text') && $request->has('quiz_answer_status')) {
					foreach ($request->quiz_answer_text as $key => $answer) {
						if (!empty($answer)) {
							$answers[] = ['text' => $answer, 'status' => $request->quiz_answer_status[$key]];
						}
					}
				}
				
				// Process answers for survey questions (radio, answers types)
				if ($request->question_group == 'survey' && $request->survey_type != 'stars' && 
					$request->has('quiz_answer_text')) {
					foreach ($request->quiz_answer_text as $key => $answer) {
						if (!empty($answer)) {
							$answers[] = ['text' => $answer];
						}
					}
				}
				
				// Process TFQ correct answer
				if ($request->question_group == 'quiz' && $request->type == 'tfq') {
					$correct = ($request->has('correct') && $request->correct == 'tfq_true') ? true : false;
				}
				$type = null;
				$surveyType = null;
				$category_id = null;
				if ($request->question_group == 'quiz') {
					$type = $request->type;
					$category_id = $request->category_id;
				} else {
					$surveyType = $request->survey_type;
				}
				$question->category_id = $request->category_id;
				$question->group = $request->question_group;
				// Strip any JavaScript/XSS vectors; question is rendered raw in quiz views.
				$question->question = HtmlSanitizer::clean($request->question);
				$question->type = $type;
				$question->survey_type = $surveyType;
				$question->correct = $correct;
				$question->answers = $answers;
				$question->explanation = $request->explanation;
				$question->difficulty = $request->difficulty;
				$question->tags = $request->has('tags') && !empty($request->tags) ? explode(',', $request->tags) : [];
				$question->save();
				$question->organizations()->sync($request->organization_id);
			} else {

				$rules = [
					'ascategory_id' => 'required|exists:categories,id',
					'subcategory_id' => 'required|exists:sub_categories,id',
					'question' => 'required|string',
					'question_group' => 'required',
				];

				$validator = Validator::make($request->all(), $rules);
				$errors = [];
				if ($validator->fails()) {
					$errors = $validator->errors()->toArray();
				}
				if (count($errors)) {
					return back()->withInput($request->all())->with('success', false)->withErrors($errors);
				}

				$answers = [];
				$correct = null;
				if (($request->has('quiz_answer_text')) && ($request->has('quiz_answer_score'))) {
					foreach ($request->quiz_answer_text as $key => $answer) {
						if ($key == 0) {
							$score = 1; // للقيمة الأولى
						} elseif ($key == 1) {
							$score = 3; // للقيمة الثانية
						} elseif ($key == 2) {
							$score = 5; // للقيمة الثالثة
						}
						$answers[] = ['text' => $answer, 'suggestion' => $request->quiz_answer_score[$key], 'score' => $score];
					}
				}

				$question->group = $request->question_group;
				$question->ascategory_id = $request->ascategory_id;
				$question->subcategory_id = $request->subcategory_id;
				// Strip any JavaScript/XSS vectors; question is rendered raw in quiz views.
				$question->question = HtmlSanitizer::clean($request->question);
				$question->answers = $answers;
				$question->explanation_QA_ar = $request->explanation_QA_ar;
				$question->explanation_QA_en = $request->explanation_QA_en;
				$question->save();
			}


			return redirect()->route('questions-bank.index')->with('success', 'StoredSuccessfully');
		}
	}

	public function destroy($id)
	{
		$question = QuestionsBank::find($id);
		if (!empty($question)) {
			$question->delete();
		}
	}
}
