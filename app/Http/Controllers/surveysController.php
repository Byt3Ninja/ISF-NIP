<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Surveys;
use App\Organization;
use App\SurveyScopes;
use App\QuestionsBank;
use App\SurveyQuestions;
use App\SurveyResponses;
use App\OrgQuestionsBank;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class surveysController extends Controller
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

	public function index()
	{
		$user = auth()->user();
		if($user->hasAnyRole('admin|subadmin|organizationmanager')){
		$orgIds = $user->organizations_ids;
		$surveys = Surveys::with('organization')
			->when(!$user->hasRole('admin') && !empty($orgIds), function ($query) use ($orgIds) {
				return $query->whereIn('organization_id', $orgIds);
			})
			->get();
		}
		
			if($user->hasAnyRole('Mentor|Registered')){
				$availableSurveys = SurveyResponses::where('user_id', $user->id)
					->where('triggered_on', 'mentorship')
					->with('survey_scope')
					->orderBy('created_at', 'desc')
					->when($user->hasRole('Registered'), function ($query) use ($user) {
						$query->where('cycle_id', $user->company?->cycle);
					})
					->get();
						$surveyQuestionsCount = SurveyQuestions::whereIn(
						'survey_id',
						$availableSurveys->pluck('survey_id')
					)
					->select('survey_id', DB::raw('count(*) as total'))
					->groupBy('survey_id')
					->pluck('total', 'survey_id');

					if($user->hasRole('Mentor')){
					$mentorshipRequests = MentorshipRequest::where('mentor_id', $user->id)
					->with('user.company')
					->orderBy('zoom_date', 'desc')
					->get();
					}else{
						$mentorshipRequests = MentorshipRequest::where('user_id', $user->id)
					->with('user.company')
					->orderBy('zoom_date', 'desc')
					->get();
					}
					
						//dd($mentorshipRequests[0], $availableSurveys[0], $surveyQuestionsCount);
					$surveys = $availableSurveys->map(function ($survey) use (
					$mentorshipRequests,
					$surveyQuestionsCount,
					$user
				) {
					$responses = json_decode($survey->responses, true) ?? [];
					$totalQuestions = $surveyQuestionsCount[$survey->survey_id] ?? 0;
					$completedQuestions = count($responses);
					$mentorship = $mentorshipRequests
						->where('zoom_date', '<=', $survey->created_at)
						->first();
						if($user->hasRole('Mentor')){
							$mentorship_user_id = $mentorship?->user?->id;
							$mentorship_user_name = trim( $mentorship?->user?->first_name_en . ' ' . $mentorship?->user?->last_name_en );
							$mentorship_user_image = $mentorship?->user?->image_url ? $mentorship?->user?->image_url : '/icons/account.svg';
							$mentorship_user_company_id = optional($mentorship?->user?->company)->id;
							$mentorship_user_company_name = optional($mentorship?->user?->company)->name_en;
							$mentorship_user_company_logo = optional($mentorship?->user?->company)->logo ? asset($mentorship?->user?->company?->logo) : '/images/logo-placeholder.png';
						}else{
							$mentorship_user_id = $mentorship?->mentor?->id;
							$mentorship_user_name = trim( $mentorship?->mentor?->first_name_en . ' ' . $mentorship?->mentor?->last_name_en );
							$mentorship_user_image = $mentorship?->mentor?->image_url ? $mentorship?->mentor?->image_url : '/icons/account.svg';
							$mentorship_user_company_id = null;
							$mentorship_user_company_name = null;
							$mentorship_user_company_logo = null;
						}
						
					return (object) [
						'id' => $survey->id,
						'title' => $survey->survey->title ?? '',
						'created_at' => $survey->created_at->format('Y-m-d H:i'),
						// 'due_date' => $survey->created_at->copy()->addDays(7)->format('Y-m-d H:i'),
						'status' => $completedQuestions === 0
							? 'Pending'
							: ($completedQuestions < $totalQuestions ? 'Pending' : 'Completed'),
						'survey_type' => ucfirst($survey->triggered_on),
						'total_questions' => $totalQuestions,
						'completed_questions' => $completedQuestions,
						'is_required' => (bool) $survey->survey->is_required,
						'user' => $mentorship
							? (object)[
								'id' => $mentorship_user_id,
								'name' => $mentorship_user_name,
								'image_url' => $mentorship_user_image,

								'company' => (object)[
									'id' => $mentorship_user_company_id,
									'name' => $mentorship_user_company_name,
									'logo_url' => $mentorship_user_company_logo,
								],
							]
							: null,
						'session' => $mentorship
							? (object)[
								'id' => $mentorship->id,
								'zoom_date' => $mentorship->zoom_date->format('Y-m-d H:i'),
							]
							: null,
					];
				});
						
				$surveys = $surveys->sortBy(function($survey) {
					$statusPriority = [
						'Pending' => 1,
						'Completed' => 2,
					];
					return $statusPriority[$survey->status] ?? 999;
				});

				$surveys = collect($surveys)->sortByDesc('created_at')->values();
			}
		
		return view('admin.surveys.index', compact('surveys'));
	}

	public function create()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$hasOrg = !empty($orgIds);

		$organizations = Organization::when($hasOrg && !empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
			return $query->whereIn('id', $orgIds);
		})->get();

		$questions = QuestionsBank::where('group', 'survey');
		if ($hasOrg && !empty($orgIds) && !$user->hasRole('admin')) {
			$organization_questions_ids = OrgQuestionsBank::whereIn('organization_id', $orgIds)->pluck('question_id')->toArray();
			$questions = $questions->whereIn('id', $organization_questions_ids);
		}
		$questions = $questions->get();
		return view('admin.surveys.create', compact('organizations', 'questions'));
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'title' => 'required|string',
			'organization_id' => 'required|exists:organizations,id',
			'description' => 'nullable|string',
			'selected_questions' => 'nullable|array',
			'selected_questions.*' => 'exists:questions_bank,id',
		]);

		$survey = new Surveys;
		$survey->title = $request->title;
		$survey->organization_id = $request->organization_id;
		$survey->description = $request->description;
		$survey->is_required = $request->is_required ?1 : 0;
		$survey->save();

		$questions_ids = $request->input('selected_questions', []);
		$survey->questions()->sync(array_map(function ($id, $key) {
			return ['row_no' => $key + 1, 'question_id' => $id];
		}, $questions_ids, array_keys($questions_ids)));

		return redirect()->route('surveys.index')->with('success', 'SurveyStoredSuccessfully');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$hasOrg = !empty($orgIds);

		$survey = Surveys::with(['organization', 'questions'])->findOrFail($id);
		$organizations = Organization::when($hasOrg && !empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
			return $query->whereIn('id', $orgIds);
		})->get();


		$availableQuestions = QuestionsBank::where('group', 'survey');
		if ($hasOrg && !empty($orgIds) && !$user->hasRole('admin')) {
			$organization_questions_ids = OrgQuestionsBank::whereIn('organization_id', $orgIds)->pluck('question_id')->toArray();
			$availableQuestions = $availableQuestions->whereIn('id', $organization_questions_ids);
		}
		$availableQuestions = $availableQuestions->select('id', 'question', 'survey_type', 'answers')->get();
		return view('admin.surveys.edit', compact('survey', 'organizations', 'availableQuestions'));
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
		$survey = Surveys::findOrFail($id);

		$this->validate($request, [
			'title' => 'required|string',
			'organization_id' => 'required|exists:organizations,id',
			'description' => 'nullable|string',
			'selected_questions' => 'nullable|array',
			'selected_questions.*' => 'exists:questions_bank,id',
		], [
			'selected_questions.array' => __('admin.SelectedQuestionsMustBeArray'),
			'selected_questions.*.exists' => __('admin.InvalidQuestionSelected'),
		]);

		$survey->title = $request->title;
		$survey->organization_id = $request->organization_id;
		$survey->description = $request->description;
		$survey->is_required = $request->is_required ? 1 : 0;
		$survey->save();

		$questions_ids = $request->input('selected_questions', []);
		$survey->questions()->sync(array_map(function ($id, $key) {
			return ['row_no' => $key + 1, 'question_id' => $id];
		}, $questions_ids, array_keys($questions_ids)));

		return redirect()->route('surveys.index')->with('success', 'SurveyUpdatedSuccessfully');
	}

	public function destroy($id)
	{
		$survey = Surveys::find($id);
		if (!empty($survey)) {
			$survey->delete();
		}
	}

	public function get_questions($id)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$hasOrg = !empty($orgIds);
		$survey = Surveys::with(['questions'])->find($id);
		if (empty($survey)) {
			return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']);
		} else {
			$questions = QuestionsBank::where('group', 'survey');
			if ($hasOrg && !empty($orgIds) && !$user->hasRole('admin')) {
				$organization_questions_ids = OrgQuestionsBank::whereIn('organization_id', $orgIds)->pluck('question_id')->toArray();
				$questions = $questions->whereIn('id', $organization_questions_ids);
			}
			$questions = $questions->get();
			return view('admin.surveys.questions', compact('survey', 'questions'));
		}
	}

	public function save_questions($id, Request $request)
	{
		$user = auth()->user();
		$survey = Surveys::find($id);
		if (empty($survey)) {
			return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']);
		} else {
			$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
			SurveyQuestions::where('survey_id', $id)->delete();
			$survey->questions()->sync(array_map(function ($id, $key) {
				return ['row_no' => $key + 1, 'question_id' => $id];
			}, $questions_ids, array_keys($questions_ids)));

			return redirect()->route('surveys.questions_manage', ['survey' => $id])->with('success', 'StoredSuccessfully');
		}
	}

	public function get_results($id, Request $request)
	{
		$user = auth()->user();
		$survey = Surveys::with(['questions'])->find($id);
		if (empty($survey)) {
			return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']);
		} else {
			$target = '';
			$responses = SurveyResponses::where('survey_id', $id);
			if ($request->has('target')) {
				$target = ucfirst($request->input('target'));
				if ($request->target == 'mentorship') {
					$responses = $responses->where('triggered_on', 'mentorship');
				} elseif ($request->target == 'training') {
					$responses = $responses->where('triggered_on', 'training');
				}
			}
			$responses = $responses->with(['user', 'survey', 'cycle', 'survey_scope'])->get();
			return view('admin.surveys.results', compact('survey', 'responses', 'target'));
		}
	}

	public function survey_taking($id)
	{
		$user = auth()->user();
		// Store the previous URL before displaying the survey
		$previousUrl = url()->previous();
		session(['previous_url' => $previousUrl]);
		if($user->hasRole('admin')){
			$responses = SurveyResponses::with(['survey'])->find($id);
			
		}elseif($user->hasAnyRole(['subadmin','organizationmanager'])){
			$orgIds = $user->organizations_ids;
			$responses = SurveyResponses::with(['survey'])->whereHas('survey', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			})->find($id)->first();
			
		}else{
			$responses = SurveyResponses::where('user_id', Auth::id())->with(['survey'])->find($id);
		}
		
		if (empty($responses)) {
			abort(404);
		} else {
			$survey = $responses->survey;
			$questions = $responses->questions;
			$my_responses = $responses->responses;
			$is_responded = false;
			if (empty($my_responses)) {
				$my_responses = collect([]);
			} else {
				$is_responded = true;
			}
			foreach ($questions as $oneQuestion) {
				$question_id = $oneQuestion->id;
				$question = $oneQuestion->question;
				$type = $oneQuestion->survey_type;
				$answers = $oneQuestion->answers;

				// تحقق إذا السؤال موجود مسبقًا
				$check = $my_responses->where('id', $question_id)->first();
				if (empty($check)) {
					$my_responses->push([
						'id' => $question_id,
						'question' => $question,
						'type' => $type,
						'answers' => $answers,
						'answer' => ''
					]);
				}
			}
			if($is_responded == true){
			$collection = collect($my_responses)->unique('id')->values();
			$page = request()->get('page', 1);
			$perPage = 9;

			$my_responses = new LengthAwarePaginator(
				$collection->forPage($page, $perPage),
				$collection->count(),
				$perPage,
				$page,
				['path' => request()->url(), 'query' => request()->query()]
			);
			}else{
				$my_responses = collect($my_responses)->unique('id')->values();
			}
			$survey_Title = $responses?->cycle?->title;
			$survey_cohort = $responses?->cycle?->cohort;

			return view('admin.clients.survey_taking', compact('responses', 'survey', 'my_responses', 'is_responded', 'survey_Title', 'survey_cohort'));
		}
	}

	public function survey_submit($id, Request $request)
	{

		$responses = SurveyResponses::where('user_id', Auth::id())->with(['survey'])->find($id);
		if (empty($responses)) {
			abort(404);
		} else {
			$missingQuestions = [];
			$questions = $responses->questions;
			foreach ($questions as $oneQuestion) {

				$question_id = $oneQuestion->id;

				if (!$request->has('question_' . $question_id)) {
					$missingQuestions[] = 'question_' . $question_id;
				} else {
					$answer = trim($request->input('question_' . $question_id));
					if ($answer || $answer == 0) {
						/** */
					} else {
						$missingQuestions[] = 'question_' . $question_id;
					}
				}
			}
			if (count($missingQuestions) > 0) {
				return back()->withInput()->with('error', 'surveyMissingQuestions')->with('missingQuestions', $missingQuestions);
			} else {
				$answers = [];
				foreach ($questions as $oneQuestion) {
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

				//$previousUrl = session('previous_url', route('core.dashboard_view'));
				$previousUrl = session('previous_url', route('surveys.index'));
				// Redirect based on the previous URL
				if (str_contains($previousUrl, 'dashboard/mentor/mentorshipSessions')) {
					return redirect()->route('mentorships.sessions_list_detailed')->with(['msg' => 'surveySubmitSuccess', 'class' => 'success']);
				} elseif (str_contains($previousUrl, 'dashboard')) {
					//return redirect()->route('core.dashboard_view')->with(['msg' => 'surveySubmitSuccess', 'class' => 'success']);
					return redirect()->route('surveys.index')->with(['success' => 'surveySubmitSuccess', 'class' => 'success']);
				}
			}
		}
	}

	public function delete_questions($id, Request $request)
	{
		$survey = Surveys::find($id);
		if (empty($survey)) {
			return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']);
		}
		$question_ids = $request->input('question_ids', []);
		if (empty($question_ids)) {
			return redirect()->route('surveys.questions_manage', ['survey' => $id])->with(['error' => 'noQuestionsSelected']);
		}
		$survey->questions()->detach($question_ids);
		return redirect()->route('surveys.questions_manage', ['survey' => $id])->with('success', 'DeletedSuccessfully');
	}

	public function list_surveys($id, Request $request)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->find($id);

			if (!empty($cycle)) {
				$organization_id = $cycle->organization_id;
				$surveys = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'training')->with(['survey'])->get();
				$surveys = $surveys->map(function ($survey) {
					$responses = $survey->responses;
					$eligibleUsers = $responses->count();
					$engagedUsers = $responses->whereNotNull('responses')->count();
					$notEngagedUsers = $responses->whereNull('responses')->count();
					$survey->eligibleUsers = $eligibleUsers;
					$survey->engagedUsers = $engagedUsers;
					$survey->notEngagedUsers = $notEngagedUsers;
					$survey->engagementRate = $eligibleUsers > 0 ? (number_format(round(($engagedUsers / $eligibleUsers * 100), 2), 2)) : 0.00;
					return $survey;
				});
				return view('admin.settings.training.surveys', compact('surveys', 'cycle'));
			} else {
				abort(404);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@list_surveys: ' . $e->getMessage());
		}
	}
	public function create_survey($id, Request $request)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->find($id);

			if (!empty($cycle)) {
				$organization_id = $cycle->organization_id;
				$surveys = Surveys::where('organization_id', $organization_id)->get();
				return view('admin.settings.training.create_survey', compact('surveys', 'cycle'));
			} else {
				abort(404);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@create_survey: ' . $e->getMessage());
		}
	}

	public function store_survey($id, Request $request)
	{
		try {
			$this->validate($request, [
				'id' => 'exists:cycles,id',
				'survey_id' => 'required|exists:surveys,id',
			]);

			$scope = new SurveyScopes;
			$scope->survey_id = $request->survey_id;
			$scope->cycle_id = $id;
			$scope->organization_id = $request->organization_id;
			$scope->scope = 'training';
			$scope->trigger_by_session = $request->has('trigger_by_session') ? true : false;
			$scope->trigger_by_due = $request->has('trigger_by_due') ? true : false;
			$scope->trigger_by_quiz = $request->has('trigger_by_quiz') ? true : false;
			$scope->save();

			return redirect()->route('trainings.surveys_list', ['id' => $id])->with('success', 'StoredSuccessfully');
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@store_survey: ' . $e->getMessage());
		}
	}

	public function edit_survey($id, $survey, Request $request)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->find($id);

			if (!empty($cycle)) {
				$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'training')->find($survey);
				if (!empty($cycle)) {
					$organization_id = $cycle->organization_id;
					$surveys = Surveys::where('organization_id', $organization_id)->get();
					return view('admin.settings.training.edit_survey', compact('surveys', 'cycle', 'scope'));
				} else {
					abort(404);
				}
			} else {
				abort(404);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@edit_survey: ' . $e->getMessage());
		}
	}

	public function update_survey($id, $survey, Request $request)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->find($id);

			if (!empty($cycle)) {
				$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'training')->find($survey);
				if (!empty($cycle)) {
					$scope->survey_id = $request->survey_id;
					$scope->trigger_by_session = $request->has('trigger_by_session') ? true : false;
					$scope->trigger_by_due = $request->has('trigger_by_due') ? true : false;
					$scope->trigger_by_quiz = $request->has('trigger_by_quiz') ? true : false;
					$scope->save();
					return back()->withInput()->with('success', 'StoredSuccessfully');
				} else {
					abort(404);
				}
			} else {
				abort(404);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@update_survey: ' . $e->getMessage());
		}
	}

	public function destroy_survey($id, $survey, Request $request)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->find($id);

			if (!empty($cycle)) {
				$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'training')->find($survey);
				if (!empty($cycle)) {
					$scope->delete();
				} else {
					abort(404);
				}
			} else {
				abort(404);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@destroy_survey: ' . $e->getMessage());
		}
	}
}
