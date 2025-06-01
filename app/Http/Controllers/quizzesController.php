<?php

namespace App\Http\Controllers;
use App\Step;
use App\Cycle;
use App\Quizzes;
use App\Training;
use App\Organization;
use App\QuestionsBank;
use App\QuizQuestions;
use App\TrainingSession;
use App\QuestionCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class quizzesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() { $this->middleware('auth'); }

	public function index(Request $request)
	{
		$user = auth()->user();
		$organization_id = $user->organization_id;

		$quizzes_q = "SELECT 
		q.*,
		o.id AS org_id,
		o.name_ar AS org_name_ar,
		o.name_en AS org_name_en, 
		t.title AS training_title,
		t.id AS training_id,
		t.dsc AS training_description,
		c.id AS cycle_id,
		c.title AS cycle_title,
		qr.id AS result_id,
		qr.user_id AS result_user_id

		FROM quizzes q
		LEFT JOIN organizations o ON q.organization_id = o.id
		LEFT JOIN trainings t ON q.training_id = t.id
		LEFT JOIN cycles c ON c.organization_id = o.id
		LEFT JOIN quiz_results qr ON qr.quiz_id = q.id
		WHERE t.deleted_at IS NULL ";
		if( $request->org_id != 0 )
		{
			$quizzes_q .= "AND q.organization_id = $request->org_id ";
		}
		if($request->status != 0){
			$quizzes_q .= "AND q.status = $request->status ";
		}
		if($request->Program_id != 0){
			$quizzes_q .= "AND c.id = $request->Program_id ";
		}

		$quizzes_q = DB::select($quizzes_q);
		$quizzes = collect($quizzes_q);

		$quizzes = $quizzes->map(function ($quiz) {
			$quiz_obj = new stdClass();
			$quiz_obj->id = $quiz->id;
			$quiz_obj->title = $quiz->title;
			$quiz_obj->organization_id = $quiz->org_id;
			$quiz_obj->training_id = $quiz->training_id;
			$quiz_obj->description = $quiz->description;
			$quiz_obj->start = $quiz->start;
			$quiz_obj->end = $quiz->end;
			$quiz_obj->time_limit = $quiz->time_limit;
			$quiz_obj->attempts = $quiz->attempts;
			$quiz_obj->retry_after = $quiz->retry_after;
			$quiz_obj->passing_criteria = $quiz->passing_criteria;
			$quiz_obj->shuffle = $quiz->shuffle;
			$quiz_obj->status = $quiz->status;
			$quiz_obj->created_at = $quiz->created_at;
			$quiz_obj->updated_at = $quiz->updated_at;

			 $quiz_obj->organization = new stdClass();
			 $quiz_obj->organization->id = $quiz->org_id;
			$quiz_obj->organization->org_name_ar = $quiz->org_name_ar;
			$quiz_obj->organization->org_name_en = $quiz->org_name_en;

			$quiz_obj->training = new stdClass();
			$quiz_obj->training->id = $quiz->training_id;
			$quiz_obj->training->title = $quiz->training_title;
			$quiz_obj->training->dsc = $quiz->training_description;

			$quiz_obj->results = new stdClass();
			$quiz_obj->results->id = $quiz->result_id;
			$quiz_obj->results->user_id = $quiz->result_user_id;


			$quiz_obj->cycle = new stdClass();
			$quiz_obj->cycle->cycle_id = $quiz->cycle_id;
			$quiz_obj->cycle->cycle_title = $quiz->cycle_title;
			return $quiz_obj;
			
		});

		
		
		

		// $quizzes2 = Quizzes::with(['organization', 'training', 'results'])->get();

		// dd($quizzes, $quizzes2);
		// if( !empty( $organization_id ) )
		// {
		// 	if( is_array( $organization_id ) ) { $quizzes = $quizzes->whereIn('organization_id', $organization_id); }
		// 	else { $quizzes = $quizzes->where('organization_id', $organization_id); }
		// }
		// if($request->org_id != 0){
		// 	$quizzes = $quizzes->where('organization_id', $request->org_id);
		// }
		// if($request->status != 0){
		// 	$quizzes = $quizzes->where('status', $request->status);
		// }
		// if($request->Program_id != 0){
		// 	// $cycle = Cycle::where('id',$request->Program_id)->first();
		// 	$get_step = Step::where('stype', 4)->where('cycle_id', $request->Program_id)->get();
			
		// 	$training_ids = TrainingSession::whereIn('step_id', $get_step->pluck('id')->toArray())->get()->filter(function($training) {
		// 		return $training->training_Address != null && is_numeric($training->training_Address);
		// 	})->pluck('training_Address')->unique()->values()->toArray();
			
		// 	$quizzes = $quizzes->whereIn('training_id', $training_ids);
		// }
		// $quizzes = $quizzes->get();

		return view('admin.quizzes.index', compact('quizzes'));
	}

	public function create()
	{
		$user = auth()->user();
		$organizations = new Organization;
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
		$organizations = $organizations->get();
		$trainings = Training::get();
		return view('admin.quizzes.create', compact('organizations', 'trainings'));
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'organization_id' => 'required|exists:organizations,id',
			'training_id' => 'required|exists:trainings,id',
			'title' => 'required|string',
			'description' => 'nullable|sometimes',
			'start' => 'required|date_format:Y-m-d H:i',
			'end' => 'required|date_format:Y-m-d H:i',
			'time_limit' => 'required|numeric|min:0',
			'passing_criteria' => 'required|numeric|min:0|max:100',
			'shuffle' => 'required|in:shuffle_true,shuffle_false',
			'status' => 'required|in:draft,published',
			'attempts' => 'required|numeric|min:0',
			'retry_after' => 'required|date_format:H:i',
		]);

		$quiz = new Quizzes;
		$quiz->title = $request->title;
		$quiz->organization_id = $request->organization_id;
		$quiz->training_id = $request->training_id;
		$quiz->description = $request->description;
		$quiz->start = $request->start;
		$quiz->end = $request->end;
		$quiz->time_limit = $request->time_limit;
		$quiz->attempts = $request->attempts;
		$quiz->retry_after = $request->retry_after;
		$quiz->passing_criteria = $request->passing_criteria;
		$quiz->shuffle = $request->shuffle == 'shuffle_true' ? true : false;
		$quiz->status = $request->status;
		$quiz->save();

		return redirect()->route('quizzes.index')->with('success', 'StoredSuccessfully');
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
		$quiz = Quizzes::with(['questions', 'organization']);
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $quiz = $quiz->where('organization_id', $user->organization_id); }
		$quiz = $quiz->find( $id );
		if( empty( $quiz ) ) { return redirect()->route('quizzes.index')->with(['error' => 'quizNotFound']); }
		else
		{
			$organizations = new Organization;
			if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $organizations = $organizations->where('id', $user->organization_id); }
			$organizations = $organizations->get();
			$trainings = Training::get();
			return view('admin.quizzes.edit', compact('quiz', 'organizations', 'trainings'));
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
		$quiz = Quizzes::with(['questions', 'organization']);
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $quiz = $quiz->where('organization_id', $user->organization_id); }
		$quiz = $quiz->find( $id );
		if( empty( $quiz ) ) { return back()->with(['error' => 'quizNotFound']); }
		else
		{
			$this->validate($request, [
				'organization_id' => 'required|exists:organizations,id',
				'training_id' => 'required|exists:trainings,id',
				'title' => 'required|string',
				'description' => 'nullable|sometimes',
				'start' => 'required|date_format:Y-m-d H:i',
				'end' => 'required|date_format:Y-m-d H:i',
				'time_limit' => 'required|numeric|min:0',
				'passing_criteria' => 'required|numeric|min:0|max:100',
				'shuffle' => 'required|in:shuffle_true,shuffle_false',
				'status' => 'required|in:draft,published',
				'attempts' => 'required|numeric|min:0',
				'retry_after' => 'required|date_format:H:i',
			]);

			$quiz->title = $request->title;
			$quiz->organization_id = $request->organization_id;
			$quiz->training_id = $request->training_id;
			$quiz->description = $request->description;
			$quiz->start = $request->start;
			$quiz->end = $request->end;
			$quiz->time_limit = $request->time_limit;
			$quiz->attempts = $request->attempts;
			$quiz->retry_after = $request->retry_after;
			$quiz->passing_criteria = $request->passing_criteria;
			$quiz->shuffle = $request->shuffle == 'shuffle_true' ? true : false;
			$quiz->status = $request->status;
			$quiz->save();

			return redirect()->route('quizzes.index')->with('success', 'StoredSuccessfully');
		}
	}

	public function destroy( $id )
	{
		$quiz = Quizzes::find( $id );
		if( !empty( $quiz ) )
		{
			$quiz->delete();
		}
	}

	public function get_questions( $id )
	{
		$user = auth()->user();
		$quiz = Quizzes::with(['questions', 'organization']);
		
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $quiz = $quiz->where('organization_id', $user->organization_id); }
		$quiz = $quiz->find( $id );
		
		if( empty( $quiz ) ) { return redirect()->route('quizzes.index')->with(['error' => 'quizNotFound']); }
		else
		{
			
			$questions = QuestionsBank::where('group', 'quiz')->with(['category']);
			if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $questions = $questions->where('organization_id', $quiz->organization_id); }
			$questions = $questions->get();
			$tags = array_unique(array_reduce($questions->pluck('tags')->toArray(), 'array_merge', []));
			return view('admin.quizzes.questions', compact('quiz', 'questions', 'tags'));
		}
	}

	public function save_questions( $id, Request $request )
	{
		$user = auth()->user();
		$quiz = Quizzes::with(['questions', 'organization']);
		if( isset( $user->organization_id ) && !empty( $user->organization_id ) ) { $quiz = $quiz->where('organization_id', $user->organization_id); }
		$quiz = $quiz->find( $id );
		if( empty( $quiz ) ) { return back()->with(['error' => 'quizNotFound']); }
		else
		{
			$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
			QuizQuestions::where('quiz_id', $id)->delete();
			$quiz->questions()->sync( array_map(function($id, $key) { return ['row_no' => $key + 1, 'question_id' => $id]; }, $questions_ids, array_keys($questions_ids)) );

			return redirect()->route('quizzes.questions_manage', ['quiz' => $id])->with('success', 'StoredSuccessfully');
		}
	}

	public function my()
	{
		$user = auth()->user();
		if( $user->hasAnyRole(['Registered']) )
		{
			if( !$user->HaveCompany ) { return redirect()->route('user.profile_view')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']); }
			if( !$user->company ) { return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']); }

			$company = $user->company;
			$cycleData = $company->cycledata;

			// if( empty( $cycleData ) ) { return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
			// if( empty( $cycleData->organization_id ) ) { return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
			if( !empty( $cycleData->organization_id ) )
			{
				$organization_id = $cycleData->organization_id;
				$get_step = Step::where('stype', 4)->where('cycle_id', $cycleData->id)->get();
				$training_ids = TrainingSession::whereIn('step_id', $get_step->pluck('id')->toArray())->get()->filter(function($training) {
					return $training->training_Address != null && is_numeric($training->training_Address);
				})->pluck('training_Address')->unique()->values()->toArray();

				$quizzes = Quizzes::where('status', 'published')
					->where('organization_id', $organization_id)
					->whereIn('training_id', $training_ids)
					->with(['training', 'myAttempts', 'questions'])
					->get();
			}
			else
			{
				$quizzes = collect([]);
			}
			return view('admin/clients/quiz/dashboard', compact('quizzes'));
		} else { return abort(401); }
	}
}
