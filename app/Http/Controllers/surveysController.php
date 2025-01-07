<?php

namespace App\Http\Controllers;

use App\Surveys;
use App\Organization;
use App\QuestionsBank;
use App\SurveyQuestions;
use App\SurveyResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class surveysController extends Controller
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
		$surveys = Surveys::get();
		return view('admin.surveys.index', compact('surveys'));
	}

	public function create()
	{
		$user = auth()->user();
		$organizations = new Organization;
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
		$organizations = $organizations->get();
		$questions = QuestionsBank::where('group', 'survey');
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $questions = $questions->where('organization_id', $user->organization_id); }
		$questions = $questions->get();
		return view('admin.surveys.create', compact('organizations', 'questions'));
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'title' => 'required|string',
			'organization_id' => 'required|exists:organizations,id',
			'description' => 'nullable|sometimes',
		]);

		$survey = new Surveys;
		$survey->title = $request->title;
		$survey->organization_id = $request->organization_id;
		$survey->description = $request->description;
		$survey->save();

		$id = $survey->id;
		$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
		SurveyQuestions::where('survey_id', $id)->delete();
		$survey->questions()->sync( array_map(function($id, $key) { return ['row_no' => $key + 1, 'question_id' => $id]; }, $questions_ids, array_keys($questions_ids)) );
		return redirect()->route('surveys.index')->with('success', 'StoredSuccessfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit( $id )
	{
		$user = auth()->user();
		$survey = Surveys::with(['questions'])->find( $id );
		if( empty( $survey ) ) { return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']); }
		else
		{
			$organizations = new Organization;
			if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
			$organizations = $organizations->get();
			$questions = QuestionsBank::where('group', 'survey');
			if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $questions = $questions->where('organization_id', $user->organization_id); }
			$questions = $questions->get();
			$survey_questions = !empty( $survey->questions ) ? $survey->questions->pluck('id')->toArray() : [];
			return view('admin.surveys.edit', compact('survey', 'organizations', 'questions', 'survey_questions'));
		}
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
		$user = auth()->user();
		$survey = Surveys::find( $id );
		if( empty( $survey ) ) { return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']); }
		else
		{
			$this->validate($request, [
				'title' => 'required|string',
				'organization_id' => 'required|exists:organizations,id',
				'description' => 'nullable|sometimes',
			]);

			$survey->title = $request->title;
			$survey->organization_id = $request->organization_id;
			$survey->description = $request->description;
			$survey->save();
			$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
			SurveyQuestions::where('survey_id', $id)->delete();
			$survey->questions()->sync( array_map(function($id, $key) { return ['row_no' => $key + 1, 'question_id' => $id]; }, $questions_ids, array_keys($questions_ids)) );
			return back()->withInput()->with('success', 'StoredSuccessfully');
		}
	}

	public function destroy( $id )
	{
		$survey = Surveys::find( $id );
		if( !empty( $survey ) )
		{
			$survey->delete();
		}
	}

	public function get_questions( $id )
	{
		$user = auth()->user();
		$organization_id = null;
		$survey = Surveys::with(['questions'])->find( $id );
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organization_id = $user->organization_id; }
		if( empty( $survey ) ) { return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']); }
		else
		{
			$questions = QuestionsBank::where('group', 'survey');
			if( !is_null( $organization_id ) ) { $questions = $questions->where('organization_id', $organization_id); }
			$questions = $questions->get();
			return view('admin.surveys.questions', compact('survey', 'questions'));
		}
	}

	public function save_questions( $id, Request $request )
	{
		$user = auth()->user();
		$survey = Surveys::find( $id );
		if( empty( $survey ) ) { return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']); }
		else
		{
			$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
			SurveyQuestions::where('survey_id', $id)->delete();
			$survey->questions()->sync( array_map(function($id, $key) { return ['row_no' => $key + 1, 'question_id' => $id]; }, $questions_ids, array_keys($questions_ids)) );

			return redirect()->route('surveys.questions', ['survey' => $id])->with('success', 'StoredSuccessfully');
		}
	}

	public function get_results( $id, Request $request ) 
	{
		$user = auth()->user();
		$organization_id = null;
		$survey = Surveys::with(['questions'])->find( $id );
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organization_id = $user->organization_id; }
		if( empty( $survey ) ) { return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']); }
		else
		{
			$target = '';
			$responses = SurveyResponses::where('survey_id', $id);
			if( $request->has('target') )
			{
				$target = ucfirst( $request->input('target') );
				if( $request->target == 'mentorship' ) { $responses = $responses->where('triggered_on', 'mentorship'); }
				elseif( $request->target == 'training' ) { $responses = $responses->where('triggered_on', 'training'); }
			}
			$responses = $responses->with(['user', 'survey', 'cycle', 'survey_scope'])->get();
			return view('admin.surveys.results', compact('survey', 'responses', 'target'));
		}
	}

	public function survey_taking( $id )
	{
		$responses = SurveyResponses::where('user_id', Auth::id())->with(['survey'])->find( $id );
		if( empty( $responses ) ) { abort(404); }
		else
		{
			$survey = $responses->survey;
			$questions = $responses->questions;
			$my_responses = $responses->responses;
			$is_responded = false;
			if( empty( $my_responses ) ) { $my_responses = collect([]); } else { $is_responded = true; }
			foreach( $questions as $oneQuestion )
			{
				$question_id = $oneQuestion->id;
				$question = $oneQuestion->question;
				$type = $oneQuestion->survey_type;
				$answers = $oneQuestion->answers;
	
				$check = $my_responses->where('id', $id)->first();
				if( empty( $check ) )
				{
					$my_responses->push([
						'id' => $question_id,
						'question' => $question,
						'type' => $type,
						'answers' => $answers,
						'answer' => ''
					]);
				}
			}
			return view('admin.clients.survey_taking', compact('responses', 'survey', 'my_responses', 'is_responded'));
		}
	}

	public function survey_submit( $id, Request $request )
	{
		
		$responses = SurveyResponses::where('user_id', Auth::id())->with(['survey'])->find( $id );
		if( empty( $responses ) ) { abort(404); }
		else
		{
			$missingQuestions = [];
			$questions = $responses->questions;
			foreach( $questions as $oneQuestion )
			{
				
				$question_id = $oneQuestion->id;
				
				if( !$request->has('question_' . $question_id) ) { $missingQuestions[] = 'question_' . $question_id; }
				else
				{
					$answer = trim( $request->input('question_' . $question_id) );
					if( $answer || $answer == 0 ) { /** */ }
					else
					{
						$missingQuestions[] = 'question_' . $question_id;
					}
				}
			}
			if( count( $missingQuestions ) > 0 ) { return back()->withInput()->with('error', 'surveyMissingQuestions')->with('missingQuestions', $missingQuestions); }
			else
			{
				$answers = [];
				foreach( $questions as $oneQuestion )
				{
					$question_id = $oneQuestion->id;
					$answers[] = [
						'id' => $oneQuestion->id,
						'question' => $oneQuestion->question,
						'type' => $oneQuestion->survey_type,
						'answers' => $oneQuestion->answers->toArray(),
						'answer' => $request->input('question_' . $question_id)
					];
				}
				$responses->responses = $answers;
				$responses->save();
				return redirect()->route('dashboard')->with( ['msg'=> 'surveySubmitSuccess', 'class' => 'success']);
			}
		}
	}
}
