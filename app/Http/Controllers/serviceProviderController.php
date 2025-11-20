<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use App\Surveys;
use App\Settings;
use App\Organization;
use App\SurveyScopes;
use App\QuestionsBank;
use App\SurveyQuestions;
use App\SurveyResponses;
use App\OrgQuestionsBank;
use App\serviceProviderRegistration;
use Illuminate\Http\Request;
use App\Mail\serviceProviderRegistrationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class serviceProviderController extends Controller
{

	public function get_media( $path = null )
	{
		try {
			$instance = Storage::disk('serviceProvider');
			if( $instance->directoryExists( $path ) ) { return response()->view('errors.404')->setStatusCode( 404 ); }
			$file = $instance->response( $path );
			$mimeType = $instance->mimeType($path);
			if( $mimeType == 'image/svg' ) { $mimeType = 'image/svg+xml'; }
			$file->headers->set('Content-Type', $mimeType);
			return $file;
		} catch( \Throwable $th ) { return response()->view('errors.404')->setStatusCode( 404 ); }
	}

	public function registration()
	{
		$setting = Settings::first();
		$logo = setting('Logo', 'images/background/logo.png');
		$termsUrl = setting('terms_url', '#') ;
		$privacyUrl = setting('privacy_url', '#');
		// if( trim( $termsUrl ) == '#' ) { $termsUrl = null; }
		// if( trim( $privacyUrl ) == '#' ) { $privacyUrl = null; }
		return view('auth.provider_register', compact('setting', 'logo', 'termsUrl', 'privacyUrl'));
	}

	public function registration_request(Request $request)
	{
		$request->validate([
			'company_name' => 'required',
			'contact_name' => 'required',
			'email' => 'required|email:rfc,dns|unique:users,email|unique:service_provider_registration,email',
			'country_code' => 'required|digits_between:1,3',
			'phone' => [
				'required',
				'digits_between:4,12',
				function( $attribute, $value, $fail ) use ( $request ) {
					$combined = '+' . $request->country_code . $value;
					$exists = DB::table('service_provider_registration')
						->where('phone', $combined)
						->first();

					if( !empty( $exists ) ) { $fail( __('auth.validate_phone_unique')); }
					elseif( !preg_match('/^\+[1-9]\d{1,14}$/', $combined) ) { $fail( __('auth.validate_phone_format')); }
				},
			],
			'terms_agreement' => 'required|in:agreed',
		], [
			'company_name.required' => __('auth.validate_company_name_required'),
			'contact_name.required' => __('auth.validate_contact_name_required'),
			'email.required' => __('auth.validate_email_required'),
			'email.email' => __('auth.validate_email_email'),
			'email.unique' => __('auth.validate_email_unique'),
			'country_code.required' => __('auth.validate_country_code_required'),
			'country_code.digits_between' => __('auth.validate_country_code_digits_between'),
			'phone.required' => __('auth.validate_phone_required'),
			'phone.digits_between' => __('auth.validate_phone_digits_between'),
			'terms_agreement.required' => __('auth.validate_terms_agreement_required'),
			'terms_agreement.in' => __('auth.validate_terms_agreement_in'),
		]);

		/* $upload_file = [];
		if( $request->hasFile('img_url') )
		{
			$upload_file = uploader( 'serviceProvider', $request->file('img_url'), '', true );
		} */

		$contact_name = $request->contact_name;
		$registration = new serviceProviderRegistration; 
		$registration->company_name = $request->company_name;
		$registration->contact_name = $request->contact_name;
		$registration->email = $request->email;
		$registration->phone = '+' . $request->country_code . $request->phone;
		$registration->status = null;
		$registration->user_id = null;
		$registration->ip_address = $request->ip();
		$registration->save();

		// send confirmation email here
		if( !in_array(env('APP_ENV', 'local'), ['local']) )
		{
			Mail::to($registration->email)->queue(new serviceProviderRegistrationMail( $contact_name ));
        }

		return redirect()->back()->with('success', __('auth.provider_registration_recorded'));
	}

	public function requests(Request $request)
	{
		dd('here to requests');
	}
	

	public function index()
	{
		dd('here to list');
		$user = auth()->user();
		$surveys = Surveys::with('organization')
			->when($user->organization_id, function ($query) use ($user) {
				return $query->where('organization_id', $user->organization_id);
			})
			->get();
		return view('admin.surveys.index', compact('surveys'));
	}

	public function create()
	{
		$user = auth()->user();
		$organization_id = null;
		if (isset($user->organization_id) && !empty($user->organization_id)) {
			$organization_id = $user->organization_id;
		}

		$organizations = Organization::when($organization_id, function ($query) use ($organization_id) {
			return $query->where('id', $organization_id);
		})->get();

		$questions = QuestionsBank::where('group', 'survey');
		if (!is_null($organization_id)) {
			$organization_questions_ids = OrgQuestionsBank::where('organization_id', $organization_id)->pluck('question_id')->toArray();
			$questions = $questions->whereIn('id', $organization_questions_ids);
			// $questions = $questions->where('organization_id', $organization_id);
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
		$survey->is_required = $request->is_required;
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
		$organization_id = null;
		if (isset($user->organization_id) && !empty($user->organization_id)) {
			$organization_id = $user->organization_id;
		}

		$survey = Surveys::with(['organization', 'questions'])->findOrFail($id);
		$organizations = Organization::when($organization_id, function ($query) use ($organization_id) {
			return $query->where('id', $organization_id);
		})->get();


		$availableQuestions = QuestionsBank::where('group', 'survey');
		if (!is_null($organization_id)) {
			$organization_questions_ids = OrgQuestionsBank::where('organization_id', $organization_id)->pluck('question_id')->toArray();
			$availableQuestions = $availableQuestions->whereIn('id', $organization_questions_ids);
			// $availableQuestions = $availableQuestions->where('organization_id', $organization_id);
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
		$survey->is_required = $request->is_required;
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
		$organization_id = null;
		$survey = Surveys::with(['questions'])->find($id);
		if (isset($user->organization_id) && !empty($user->organization_id)) {
			$organization_id = $user->organization_id;
		}
		if (empty($survey)) {
			return redirect()->route('surveys.index')->with(['error' => 'surveyNotFound']);
		} else {
			$questions = QuestionsBank::where('group', 'survey');
			if (!is_null($organization_id)) {
				$organization_questions_ids = OrgQuestionsBank::where('organization_id', $organization_id)->pluck('question_id')->toArray();
				$questions = $questions->whereIn('id', $organization_questions_ids);
				// $questions = $questions->where('organization_id', $organization_id);
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
		$organization_id = null;
		$survey = Surveys::with(['questions'])->find($id);
		if (isset($user->organization_id) && !empty($user->organization_id)) {
			$organization_id = $user->organization_id;
		}
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
		// Store the previous URL before displaying the survey
		$previousUrl = url()->previous();
		session(['previous_url' => $previousUrl]);

		$responses = SurveyResponses::where('user_id', Auth::id())->with(['survey'])->find($id);
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

				$check = $my_responses->where('id', $id)->first();
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
			return view('admin.clients.survey_taking', compact('responses', 'survey', 'my_responses', 'is_responded'));
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

				$previousUrl = session('previous_url', route('core.dashboard_view'));
				// Redirect based on the previous URL
				if (str_contains($previousUrl, 'dashboard/mentor/mentorshipSessions')) {
					return redirect()->route('mentorships.sessions_list_detailed')->with(['msg' => 'surveySubmitSuccess', 'class' => 'success']);
				} elseif (str_contains($previousUrl, 'dashboard')) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'surveySubmitSuccess', 'class' => 'success']);
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
			$organization_id = auth()->user()->organization_id;
			$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
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
			$organization_id = auth()->user()->organization_id;
			$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
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
			$organization_id = auth()->user()->organization_id;
			$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
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
			$organization_id = auth()->user()->organization_id;
			$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
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
			$organization_id = auth()->user()->organization_id;
			$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
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
