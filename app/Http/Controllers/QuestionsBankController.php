<?php

namespace App\Http\Controllers;
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
		return view('admin.questionsBank.index', compact('questions'));
	}

	public function create()
	{
		$user = auth()->user();
		$organizations = new Organization;
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
		$organizations = $organizations->with(['questionsCategories'])->get();
		return view('admin.questionsBank.create', compact('organizations'));
	}

	public function store(Request $request)
	{
		if( $request->has('quiz_answer_status') )
		{
			$quiz_answer_status = [];
			foreach( $request->quiz_answer_status as $status )
			{
				$quiz_answer_status[] = intval( $status ) == 1 || $status == 'true' ? true : false;
			}
			$request->merge(['quiz_answer_status' => $quiz_answer_status]);
		}

		$validator = Validator::make($request->all(), [
			'organization_id' => 'required|exists:organizations,id',
			'category_id' => 'required|exists:question_categories,id',
			'difficulty' => 'required|in:easy,medium,hard',
			'question' => 'required|string',
			'type' => 'required|in:mcq,tfq',
			'correct' => 'required_if:type,tfq|in:tfq_true,tfq_false',
			'explanation' => 'nullable|sometimes',
			'quiz_answer_text' => 'required_if:type,mcq|array',
			"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
			'quiz_answer_status' => 'required_if:type,mcq|array',
			"quiz_answer_status.*" => "required_if:type,mcq|boolean",
		]);
		$errors = [];
		if( $validator->fails() ) { $errors = $validator->errors()->toArray(); }
		if( count( $errors ) ) { return back()->withInput( $request->all() )->with('success', false)->withErrors( $errors ); }
		$this->validate($request, [
			'organization_id' => 'required|exists:organizations,id',
			'category_id' => 'required|exists:question_categories,id',
			'difficulty' => 'required|in:easy,medium,hard',
			'question' => 'required|string',
			'type' => 'required|in:mcq,tfq',
			'correct' => 'required_if:type,tfq|in:tfq_true,tfq_false',
			'explanation' => 'nullable|sometimes',
			'quiz_answer_text' => 'required_if:type,mcq|array',
			"quiz_answer_text.*" => "required_if:type,mcq|nullable|string",
			'quiz_answer_status' => 'required_if:type,mcq|array',
			"quiz_answer_status.*" => "required_if:type,mcq|boolean",
		]);
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
		$question = new QuestionsBank;
		$question->organization_id = $request->organization_id;
		$question->category_id = $request->category_id;
		$question->question = $request->question;
		$question->type = $request->type;
		$question->correct = $correct;
		$question->answers = $answers;
		$question->explanation = $request->explanation;
		$question->difficulty = $request->difficulty;
		$question->tags = $request->has('tags') && !empty( $request->tags ) ? explode(',', $request->tags) : [];
		$question->save();

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
		return view('admin.questionsBank.edit', compact('organizations', 'question'));
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
				'category_id' => 'required|exists:question_categories,id',
				'difficulty' => 'required|in:easy,medium,hard',
				'question' => 'required|string',
				'type' => 'required|in:mcq,tfq',
				'correct' => 'required_if:type,tfq|in:tfq_true,tfq_false',
				'explanation' => 'nullable|sometimes',
				'quiz_answer_text' => 'required_if:type,mcq|array',
				'quiz_answer_status' => 'required_if:type,mcq|array',
			];
			if( $request->type == 'mcq' )
			{
				$rules["quiz_answer_text.*"] = "required_if:type,mcq|string";
				$rules["quiz_answer_status.*"] = "required_if:type,mcq|sometimes|boolean";
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
			$question->organization_id = $request->organization_id;
			$question->category_id = $request->category_id;
			$question->question = $request->question;
			$question->type = $request->type;
			$question->correct = $correct;
			$question->answers = $answers;
			$question->explanation = $request->explanation;
			$question->difficulty = $request->difficulty;
			$question->tags = $request->has('tags') && !empty( $request->tags ) ? explode(',', $request->tags) : [];
			$question->save();
	
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
