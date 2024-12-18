<?php

namespace App\Http\Controllers;
use App\Category;
use App\SubCategory;
use App\Organization;
use App\QuestionsBank;
use App\QuestionCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionsBankController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() { $this->middleware('auth'); }

	public function index()
	{
		$user = auth()->user();
		$organization_id = $user->organization_id;
		$questions = QuestionsBank::with(['organization']);
		if( !empty( $organization_id ) )
		{
			if( is_array( $organization_id ) ) { $questions = $questions->whereIn('organization_id', $organization_id); }
			else { $questions = $questions->where('organization_id', $organization_id); }
		}
		$questions = $questions->get();

		$categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();
		return view('admin.questionsBank.index', compact('questions'));
	}

	public function create()
	{
		$user = auth()->user();
		$organizations = new Organization;
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
		$organizations = $organizations->with(['questionsCategories'])->get();

		$categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();

		return view('admin.questionsBank.create', compact('organizations','categories', 'subcategories'));
	}

	public function store(Request $request)
	{
		if($request->question_group !='assessment'){
		if( $request->has('quiz_answer_status') )
		{
			$quiz_answer_status = [];
			foreach( $request->quiz_answer_status as $status )
			{
				$quiz_answer_status[] = intval( $status ) == 1 || $status == 'true' ? true : false;
			}
			$request->merge(['quiz_answer_status' => $quiz_answer_status]);
		}

		$rules = [
			'organization_id' => 'required|exists:organizations,id',
			'category_id' => 'required_if:question_group,quiz|exists:question_categories,id',
			'difficulty' => 'required_if:question_group,quiz|in:easy,medium,hard',
			'question' => 'required|string',
			'type' => 'required|in:mcq,tfq',
			'question_group' => 'required|in:quiz,survey',
			'correct' => 'required_if:question_group,quiz|required_if:type,tfq|in:tfq_true,tfq_false',
			'explanation' => 'nullable|sometimes',
		];
		if( $request->question_group == 'quiz' )
		{
			$rules = array_merge( $rules, [
				'quiz_answer_text' => 'required_if:type,mcq|array',
				"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
				'quiz_answer_status' => 'required_if:type,mcq|array',
				"quiz_answer_status.*" => "required_if:type,mcq|boolean",
			]);
		}
		if( $request->question_group == 'survey' )
		{
			$text_status = $request->survey_type != 'stars' ?  'required_if:type,mcq' : 'nullable';
			$rules = array_merge( $rules, [
				'quiz_answer_text' => 'required_if:type,mcq|array',
				"quiz_answer_text.*" => "$text_status|string",
			]);
		}
		$validator = Validator::make($request->all(), $rules);
		$errors = [];
		if( $validator->fails() ) { $errors = $validator->errors()->toArray(); }
		if( count( $errors ) ) { return back()->withInput( $request->all() )->with('success', false)->withErrors( $errors ); }
		$answers = [];
		$correct = null;
		if( $request->type == 'mcq' && ( $request->has('quiz_answer_text') ) && ( $request->has('quiz_answer_status') )  )
		{
			foreach( $request->quiz_answer_text as $key => $answer ) { $answers[] = ['text' => $answer, 'status' => $request->quiz_answer_status[$key]]; }
		}
		if( $request->type == 'tfq' )
		{
			$correct = ( $request->has('correct') && $request->correct == 'tfq_true' ) ? true : false;
		}
		$type = null;
		$surveyType = null;
		$category_id = null;
		$question = new QuestionsBank;
		$question->organization_id = $request->organization_id;
		$question->group = $request->question_group;
		if( $request->question_group == 'quiz' )
		{
			$type = $request->type;
			$category_id = $request->category_id;
		} else { $surveyType = $request->survey_type; }

		$question->category_id = $request->category_id;
		$question->question = $request->question;
		$question->type = $type;
		$question->survey_type = $surveyType;
		$question->correct = $correct;
		$question->answers = $answers;
		$question->explanation = $request->explanation;
		$question->difficulty = $request->difficulty;
		$question->tags = $request->has('tags') && !empty( $request->tags ) ? explode(',', $request->tags) : [];
		$question->save();
	}else{

		$rules = [
			'ascategory_id' => 'required|exists:categories,id',
			'subcategory_id' => 'required|exists:sub_categories,id',
			'question' => 'required|string',
			'question_group' => 'required',
		];
		
		$validator = Validator::make($request->all(), $rules);
		$errors = [];
		if( $validator->fails() ) { $errors = $validator->errors()->toArray(); }
		if( count( $errors ) ) { return back()->withInput( $request->all() )->with('success', false)->withErrors( $errors ); }

		$answers = [];
		$correct = null;
		if( ( $request->has('quiz_answer_text') ) && ( $request->has('quiz_answer_score') )  )
		{
			
			foreach( $request->quiz_answer_text as $key => $answer ) { 
				if ($key == 0) {
					$score = 1; // للقيمة الأولى
				} elseif ($key == 1) {
					$score = 3; // للقيمة الثانية
				} elseif ($key == 2) {
					$score = 5; // للقيمة الثالثة
				}
				$answers[] = ['text' => $answer, 'suggestion' => $request->quiz_answer_score[$key], 'score' => $score ]; }
		}

		$question = new QuestionsBank;
		$question->group = $request->question_group;
		$question->ascategory_id = $request->ascategory_id;
		$question->subcategory_id = $request->subcategory_id;
		$question->question = $request->question;
		$question->answers = $answers;
		$question->explanation_QA_ar = $request->explanation_QA_ar;
		$question->explanation_QA_en = $request->explanation_QA_en;
		$question->save();




	}

		return redirect()->route('questions-bank.index')->with('success', 'StoredSuccessfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit( $id )
	{
		$question = QuestionsBank::find( $id );
		$user = auth()->user();
		$organizations = new Organization;
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
		$organizations = $organizations->with(['questionsCategories'])->get();
		$categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();
		return view('admin.questionsBank.edit', compact('organizations', 'question','categories', 'subcategories'));
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
		$question = QuestionsBank::find( $id );
		if( empty( $question ) ) { return back()->withInput()->with(['error' => 'questionNotFound']); }
		else
		{
			if($request->question_group !='assessment'){
			if( $request->has('quiz_answer_status') )
			{
				$quiz_answer_status = [];
				foreach( $request->quiz_answer_status as $status )
				{
					$quiz_answer_status[] = intval( $status ) == 1 || $status == 'true' ? true : false;
				}
				$request->merge(['quiz_answer_status' => $quiz_answer_status]);
			}

			$rules = [
				'organization_id' => 'required|exists:organizations,id',
				'category_id' => 'required_if:question_group,quiz|exists:question_categories,id',
				'difficulty' => 'required_if:question_group,quiz|in:easy,medium,hard',
				'question' => 'required|string',
				'type' => 'required|in:mcq,tfq',
				'question_group' => 'required|in:quiz,survey',
				'correct' => 'required_if:question_group,quiz|required_if:type,tfq|in:tfq_true,tfq_false',
				'explanation' => 'nullable|sometimes',
			];
			if( $request->question_group == 'quiz' )
			{
				$rules = array_merge( $rules, [
					'quiz_answer_text' => 'required_if:type,mcq|array',
					"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
					'quiz_answer_status' => 'required_if:type,mcq|array',
					"quiz_answer_status.*" => "required_if:type,mcq|boolean",
				]);
			}
			if( $request->question_group == 'survey' )
			{
				$text_status = $request->survey_type != 'stars' ?  'required_if:type,mcq' : 'nullable';
				$rules = array_merge( $rules, [
					'quiz_answer_text' => 'required_if:type,mcq|array',
					"quiz_answer_text.*" => "$text_status|string",
				]);
			}
			$this->validate($request, $rules);
			$answers = [];
			$correct = null;
			if( $request->type == 'mcq' && ( $request->has('quiz_answer_text') ) && ( $request->has('quiz_answer_status') )  )
			{
				foreach( $request->quiz_answer_text as $key => $answer ) { $answers[] = ['text' => $answer, 'status' => $request->quiz_answer_status[$key]]; }
			}
			if( $request->type == 'tfq' )
			{
				$correct = ( $request->has('correct') && $request->correct == 'tfq_true' ) ? true : false;
			}
			$type = null;
			$surveyType = null;
			$category_id = null;
			if( $request->question_group == 'quiz' )
			{
				$type = $request->type;
				$category_id = $request->category_id;
			} else { $surveyType = $request->survey_type; }
			$question->organization_id = $request->organization_id;
			$question->category_id = $request->category_id;
			$question->group = $request->question_group;
			$question->question = $request->question;
			$question->type = $type;
			$question->survey_type = $surveyType;
			$question->correct = $correct;
			$question->answers = $answers;
			$question->explanation = $request->explanation;
			$question->difficulty = $request->difficulty;
			$question->tags = $request->has('tags') && !empty( $request->tags ) ? explode(',', $request->tags) : [];
			$question->save();
	

		}else{

			$rules = [
				'ascategory_id' => 'required|exists:categories,id',
				'subcategory_id' => 'required|exists:sub_categories,id',
				'question' => 'required|string',
				'question_group' => 'required',
			];
			
			$validator = Validator::make($request->all(), $rules);
			$errors = [];
			if( $validator->fails() ) { $errors = $validator->errors()->toArray(); }
			if( count( $errors ) ) { return back()->withInput( $request->all() )->with('success', false)->withErrors( $errors ); }
	
			$answers = [];
			$correct = null;
			if( ( $request->has('quiz_answer_text') ) && ( $request->has('quiz_answer_score') )  )
			{
				foreach( $request->quiz_answer_text as $key => $answer ) { 
					if ($key == 0) {
						$score = 1; // للقيمة الأولى
					} elseif ($key == 1) {
						$score = 3; // للقيمة الثانية
					} elseif ($key == 2) {
						$score = 5; // للقيمة الثالثة
					}
					$answers[] = ['text' => $answer, 'suggestion' => $request->quiz_answer_score[$key], 'score' => $score]; }
			}
	
			$question->group = $request->question_group;
			$question->ascategory_id = $request->ascategory_id;
			$question->subcategory_id = $request->subcategory_id;
			$question->question = $request->question;
			$question->answers = $answers;
			$question->explanation_QA_ar = $request->explanation_QA_ar;
			$question->explanation_QA_en = $request->explanation_QA_en;
			$question->save();
	
	
	
	
		}

			
			return back()->withInput()->with('success', 'StoredSuccessfully');
		}





	}

	public function destroy( $id )
	{
		$question = QuestionsBank::find( $id );
		if( !empty( $question ) )
		{
			$question->delete();
		}
	}
}
