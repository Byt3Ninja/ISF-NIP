<?php

namespace App\Http\Controllers;

use App\Step;
use stdClass;
use App\Cycle;
use App\Quizzes;
use App\Training;
use App\QuizResults;
use App\Organization;
use App\QuestionsBank;
use App\QuizQuestions;
use App\QuizResponses;
use App\TrainingSession;
use App\QuestionCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class quizzesController extends Controller
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
		$orgIds = $user->organizations_ids;

		$quizzes_q = "SELECT 
		q.id AS quiz_id,
		q.title AS quiz_title,
		q.organization_id AS quiz_organization_id,
		q.training_id AS quiz_training_id,
		q.description AS quiz_description,
		q.start AS quiz_start,
		q.end AS quiz_end,
		q.time_limit AS quiz_time_limit,
		q.attempts AS quiz_attempts,
		q.retry_after AS quiz_retry_after,
		q.passing_criteria AS quiz_passing_criteria,
		q.shuffle AS quiz_shuffle,
		q.status AS quiz_status,
		q.created_at AS quiz_created_at,
		q.updated_at AS quiz_updated_at,
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
		if ($request->org_id != 0) {
			$quizzes_q .= "AND q.organization_id = ? ";
			$bindings[] = $request->org_id;
		}
		if ($request->status != 0) {
			$quizzes_q .= "AND q.status = ? ";
			$bindings[] = $request->status;
		}
		if ($request->Program_id != 0) {
			$quizzes_q .= "AND c.id = ? ";
			$bindings[] = $request->Program_id;
		}
		if(!$user->hasRole('admin') && !empty($orgIds)){
			$quizzes_q .= "AND o.id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
		}

		$quizzes_q = DB::select($quizzes_q, $bindings ?? []);
		$quizzes = collect($quizzes_q);

		$quizzes = $quizzes->map(function ($quiz) {
			$quiz_obj = new stdClass();
			$quiz_obj->id = $quiz->quiz_id;
			$quiz_obj->title = $quiz->quiz_title;
			$quiz_obj->organization_id = $quiz->quiz_organization_id;
			$quiz_obj->training_id = $quiz->quiz_training_id;
			$quiz_obj->description = $quiz->quiz_description;
			$quiz_obj->start = $quiz->quiz_start;
			$quiz_obj->end = $quiz->quiz_end;
			$quiz_obj->time_limit = $quiz->quiz_time_limit;
			$quiz_obj->attempts = $quiz->quiz_attempts;
			$quiz_obj->retry_after = $quiz->quiz_retry_after;
			$quiz_obj->passing_criteria = $quiz->quiz_passing_criteria;
			$quiz_obj->shuffle = $quiz->quiz_shuffle;
			$quiz_obj->status = $quiz->quiz_status;
			$quiz_obj->created_at = $quiz->quiz_created_at;
			$quiz_obj->updated_at = $quiz->quiz_updated_at;

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
		$quizzes = $quizzes->unique('id')->values();
		
		return view('admin.quizzes.index', compact('quizzes'));
	}

	public function create()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$organizations = Organization::query();
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$organizations = $organizations->whereIn('id', $orgIds);
		}
		$organizations = $organizations->get();
		$trainings = Training::visibleToUser()->get();
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
	public function edit($id)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$quiz = Quizzes::with(['questions', 'organization']);
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$quiz = $quiz->whereIn('organization_id', $orgIds);
		}
		$quiz = $quiz->find($id);
		if (empty($quiz)) {
			return redirect()->route('quizzes.index')->with(['error' => 'quizNotFound']);
		} else {
			$organizations = Organization::query();
			if (!$user->hasRole('admin') && !empty($orgIds)) {
				$organizations = $organizations->whereIn('id', $orgIds);
			}
			$organizations = $organizations->get();
			$trainings = Training::visibleToUser()->get();
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
		$orgIds = $user->organizations_ids;
		$quiz = Quizzes::with(['questions', 'organization']);
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$quiz = $quiz->whereIn('organization_id', $orgIds);
		}
		$quiz = $quiz->find($id);
		if (empty($quiz)) {
			return back()->with(['error' => 'quizNotFound']);
		} else {
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

	public function destroy($id)
	{
		$quiz = Quizzes::find($id);
		if (!empty($quiz)) {
			$quiz->delete();
		}
	}

	public function get_questions($id)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$quiz = Quizzes::with(['questions', 'organization']);

		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$quiz = $quiz->whereIn('organization_id', $orgIds);
		}
		$quiz = $quiz->find($id);

		if (empty($quiz)) {
			return redirect()->route('quizzes.index')->with(['error' => 'quizNotFound']);
		} else {
			$questions = QuestionsBank::where('group', 'quiz')->with(['category']);
			if (!$user->hasRole('admin') && !empty($orgIds)) {
				$questions = $questions->whereHas('organizations', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			}
			$questions = $questions->get();
			$tags = array_unique(array_reduce($questions->pluck('tags')->toArray(), 'array_merge', []));
			return view('admin.quizzes.questions', compact('quiz', 'questions', 'tags'));
		}
	}

	public function save_questions($id, Request $request)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$quiz = Quizzes::with(['questions', 'organization']);
		if (!$user->hasRole('admin') && !empty($orgIds)) {
			$quiz = $quiz->whereIn('organization_id', $orgIds);
		}
		$quiz = $quiz->find($id);
		if (empty($quiz)) {
			return back()->with(['error' => 'quizNotFound']);
		} else {
			$questions_ids = $request->has('questions_ids') ? $request->questions_ids : [];
			QuizQuestions::where('quiz_id', $id)->delete();
			$quiz->questions()->sync(array_map(function ($id, $key) {
				return ['row_no' => $key + 1, 'question_id' => $id];
			}, $questions_ids, array_keys($questions_ids)));

			return redirect()->route('quizzes.questions_manage', ['quiz' => $id])->with('success', 'StoredSuccessfully');
		}
	}

	public function delete_questions($id, Request $request)
	{
		$quiz = Quizzes::find($id);
		if (empty($quiz)) {
			return back()->with(['error' => 'quizNotFound']);
		}
		$question_ids = $request->input('question_ids', []);
		if (empty($question_ids)) {
			return redirect()->route('quizzes.questions_manage', ['quiz' => $id])->with(['error' => 'noQuestionsSelected']);
		}
		$quiz->questions()->detach($question_ids);
		return redirect()->route('quizzes.questions_manage', ['quiz' => $id])->with('success', 'DeletedSuccessfully');
	}

	public function my()
	{
		$user = auth()->user();
		if ($user->hasAnyRole(['Registered'])) {
			// if (!$user->HaveCompany) {
			// 	return redirect()->route('user.profile_Edit')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
			// }
			// if (!$user->company) {
			// 	return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
			// }

			$company = $user?->company;
			$cycleData = $company?->cycledata;

			// if( empty( $cycleData ) ) { return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
			// if( empty( $cycleData->organization_id ) ) { return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }

			QuizResults::refresh_my_attempts();
			if (!empty($cycleData?->organization_id))
			{
				$organization_id = $cycleData->organization_id;
				$get_step = Step::where('stype', 4)->where('cycle_id', $cycleData->id)->get();
				$training_ids = TrainingSession::whereIn('step_id', $get_step->pluck('id')->toArray())->get()->filter(function ($training) {
					return $training->training_Address != null && is_numeric($training->training_Address);
				})->pluck('training_Address')->unique()->values()->toArray();

				$quizzes = Quizzes::where('status', 'published')
					->where('organization_id', $organization_id)
					->whereIn('training_id', $training_ids)
					->with(['training', 'myAttempts', 'questions'])
					->get();
			} else {
				$quizzes = collect([]);
			}

			$quizzes = $quizzes->map(function ( $quiz ) {
				$in_progress = $quiz->myAttempts->where('status', false)->first() ? true : false;
				$quiz->in_progress = $in_progress;
				$quiz->questions = $quiz->questions;
				return $quiz;
			})->filter(function( $quiz ) {
				return $quiz->questions->count() > 0;
			})->values();
			return view('admin/clients/quiz/dashboard', compact('quizzes'));
		} else {
			return abort(401);
		}
	}
	public function doQuiz($id, $quizId, Request $request)
	{
		try {
			$user = auth()->user();
			QuizResults::refresh_my_attempts();
			$last_attempt = QuizResults::where('quiz_id', $quizId)->where('user_id', $user->id)->with(['quiz'])->orderBy('created_at', 'desc')->first();
			if( !empty( $last_attempt ) )
			{
				$attempt_quiz = $last_attempt->quiz;
				if( !empty( $attempt_quiz ) )
				{
					$retryAfter = Carbon::createFromFormat('H:i', $attempt_quiz->retry_after);
					$last = !empty($last_attempt->ended_at) ? $last_attempt->ended_at : $last_attempt->updated_at;
					$available_at = $last->addHours($retryAfter->hour)->addMinutes($retryAfter->minute);
					$available = now() >= $available_at ? true : false;
					if( $available == true )
					{
						return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizNotYetAvailable'), 'class' => 'danger']);
					}
				}
			}
			if ($user->hasAnyRole(['Registered'])) {
				if (!$user->HaveCompany) {
					return redirect()->route('user.profile_view')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
				}
				if (!$user->company) {
					return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
				}

				$company = $user->company;
				$cycleData = $company->cycledata;

				if (empty($cycleData)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}
				if (empty($cycleData->organization_id)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}

				$organization_id = $cycleData->organization_id;
				$quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail($quizId);
				$training = $quiz->training;
				if (empty($training)) {
					return abort(404);
				}

				if ($request->has('response') && $request->has('answer')) {
					$response = QuizResponses::where('user_id', $user->id)->find($request->input('response'));
					if (!empty($response)) {
						$result = $response->result;
						$time_limit = $result->started_at->copy()->addMinutes($quiz->time_limit);
						$remaining = $time_limit->diffInSeconds(now());
						if ($remaining > 0) {
							$answer = $request->input('answer');
							$question = $response->question;
							if ($question->type == 'tfq') {
								$answer = $answer === "true" || $answer === true || $answer === 1 || $answer === '1' ? true : false;
								$response->response = $answer;
								$response->status = $answer == $question->correct;
								$response->save();
							} elseif ($question->type == 'mcq') {
								$correct_answers = $question->answers->where('status', true)->map(function ($item) {
									return md5($item['text'] . '+' . $item['status']);
								})->values()->toArray();

								$response->response = $answer;
								$response->status = in_array($answer, $correct_answers);
								$response->save();
							}
							$quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail($quizId);
						} else {
							$result->status = true;
							$result->ended_at = $time_limit->format('Y-m-d H:i:s');
							$result->save();
							return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizTimesUp'), 'class' => 'danger']);
						}
					}
				}
				$questions = $quiz->questions;
				if (empty($questions) || $questions->count() == 0) {
					return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizNoQuestions'), 'class' => 'danger']);
				}
				if ($quiz->shuffle) {
					$questions = $questions->shuffle();
				}
				$attempts = $quiz->myAttempts;
				foreach ($attempts as $index => $attempt) {
					if (!$attempt->status) {
						if ($attempt->started_at->diffInMinutes(now()) > $quiz->time_limit) {
							$attempt->status = true;
							$attempt->ended_at = $attempt->started_at->copy()->addMinutes($quiz->time_limit);
							$attempt->save();
							$attempts[$index]->status = $attempt->status;
							$attempts[$index]->ended_at = $attempt->ended_at;
						}
					}
				}
				$failures = $attempts->where('score', '<', $quiz->passing_criteria);
				$success = $attempts->where('score', '>=', $quiz->passing_criteria);
				if ($success->count() > 0 || $failures->where('status', true)->count() >= $quiz->attempts) {
					return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizAlreadyCompleted'), 'class' => 'danger']);
				}

				$result = $attempts->where('status', false)->first();
				if (!empty($result)) {
					$responses = $result->responses->where('user_id', $user->id);
				} else {
					$result = new QuizResults;
					$result->quiz_id = $quiz->id;
					$result->user_id = $user->id;
					$result->score = 0;
					$result->started_at = now()->format('Y-m-d H:i:s');
					$result->ended_at = null;
					$result->status = false;
					$result->save();

					$responses = [];
					foreach ($questions as $question) {
						$responses[] = [
							'result_id' => $result->id,
							'quiz_id' => $quiz->id,
							'question_id' => $question->id,
							'user_id' => $user->id,
							'response' => null,
							'status' => false,
						];
					}
					if (count($responses) > 0) {
						QuizResponses::insert($responses);
					}
					$responses = QuizResponses::where('quiz_id', $quiz->id)->where('user_id', $user->id)->where('result_id', $result->id)->get();
				}
				$time_limit = $result->started_at->copy()->addMinutes($quiz->time_limit);
				$remaining = $time_limit->diffInSeconds(now());

				if ($remaining > 0) {
					$response = $responses->whereNull('response')->first();
					if (empty($response)) {
						$answers = QuizResponses::where('quiz_id', $quizId)->where('user_id', $user->id)->where('result_id', $result->id)->get();
						$score = $answers->where('status', true)->count();
						$totalAnswers = $answers->count();
						if ($totalAnswers > 0) {
							$result->score = round(floatval($score / $totalAnswers), 2) * 100;
						} else {
							$result->score = 0;
						}
						$result->status = true;
						$result->ended_at = now()->format('Y-m-d H:i:s');
						$result->save();
						return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizCompleted'), 'class' => 'success']);
					}
					$question = $response->question;
					if (empty($question)) {
						return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizQuestionNotFound'), 'class' => 'danger']);
					}

					$diff = now()->diff($time_limit);
					$time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
					
					// Calculate progress data
					$total_questions = $questions->count();
					$answered_questions = $responses->whereNotNull('response')->count();
					$current_question = $answered_questions + 1;
					
					return view('admin/clients/quiz/taking', compact('quiz', 'training', 'questions', 'question', 'response', 'time_remaining', 'total_questions', 'answered_questions', 'current_question'));
				} else {
					$result->status = true;
					$result->ended_at = $time_limit->format('Y-m-d H:i:s');
					$result->save();
					return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizTimesUp'), 'class' => 'danger']);
				}
			} else {
				return abort(401);
			}
		} catch (\Throwable $e) {
			Log::error('Error in quizzesController@doQuiz: ' . $e->getMessage());
			return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizStartError'), 'class' => 'danger']);
		}
	}
	public function myQuiz($id, $quizId, Request $request)
	{
		try {
			$user = auth()->user();
			QuizResults::refresh_my_attempts();
			if ($user->hasAnyRole(['Registered'])) {
				if (!$user->HaveCompany) {
					return redirect()->route('user.profile_view')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
				}
				if (!$user->company) {
					return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
				}

				$company = $user->company;
				$cycleData = $company->cycledata;

				if (empty($cycleData)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}
				if (empty($cycleData->organization_id)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}

				$organization_id = $cycleData->organization_id;
				
				$quiz = Quizzes::where('status', 'published')->where('training_id', $id)
				->where('organization_id', $organization_id)
				->where('id', $quizId)
				->with(['training', 'myAttempts', 'questions'])
				->first();
				if(empty($quiz)){
					return redirect()->back()->with(['msg' => 'Error opening the selected form.', 'class' => 'danger']);
				}
				$questions = $quiz->questions;
				$tags = $questions->flatMap(function ($question) {
					return $question->tags;
				})->unique()->values()->toArray();
				$training = $quiz->training;
				if (empty($training)) {
					return abort(404);
				}
				$available = true;
				$available_at = null;
				$allowActions = false;
				$time_remaining = null;
				$attempts = $quiz->myAttempts;
				foreach ($attempts as $index => $attempt) {
					if (!$attempt->status) {
						if ($attempt->started_at->diffInMinutes(now()) > $quiz->time_limit) {
							$attempt->status = true;
							$attempt->ended_at = $attempt->started_at->copy()->addMinutes($quiz->time_limit);
							$attempt->save();
							$attempts[$index]->status = $attempt->status;
							$attempts[$index]->ended_at = $attempt->ended_at;
						}
					}
				}
				$active_now = null;
				$get_active = $attempts->where('status', false)->first();
				if (!empty($get_active)) {
					$active_now = $get_active->id;
				}
				$failures = $attempts->where('score', '<', $quiz->passing_criteria);
				$success = $attempts->where('score', '>=', $quiz->passing_criteria);
				if ($failures->count() >= $quiz->attempts || $success->count() > 0) {
					$allowActions = true;
				}
				$available = now() > $quiz->start && now() < $quiz->end ? true : false;
				$completion_percentage = $training->progress()['percentage'];
				$available = $available && $completion_percentage >= 100 ? true : false;
				if ($available && $attempts->count() == 0) {
					$available = true;
				} else {
					$available = $available && $failures->count() < $quiz->attempts ? true : false;
					$available = $available && $success->count() == 0 ? true : false;
					$lastAttempt = $attempts->last();
					if ($lastAttempt && $failures->count() < $quiz->attempts) {
						if ($lastAttempt->score < $quiz->passing_criteria) {
							$retryAfter = Carbon::createFromFormat('H:i', $quiz->retry_after);
							$last = !empty($lastAttempt->ended_at) ? $lastAttempt->ended_at : $lastAttempt->updated_at;
							$available_at = $last->addHours($retryAfter->hour)->addMinutes($retryAfter->minute);
							$available = $available && now() >= $available_at ? true : false;
						}
					}
				}
				if ($quiz->start > now()) {
					$diff = $quiz->start->diff(now());
					$time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
				} elseif (!empty($available_at) && now() < $available_at) {
					$diff = $available_at->diff(now());
					$time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
				}
				$quiz_status = null;
				$time_now = now()->format('Y-m-d H:i:s');
				$quiz->in_progress = $attempts->where('status', false)->first() ? true : false;
				if( $attempts->where('score', '>=', $quiz->passing_criteria)->count() >= 1 ) { $quiz_status = 'passed'; }
				elseif( $time_now > $quiz->end->format('Y-m-d H:i:s') && $attempts->count() == 0 ) { $quiz_status = 'notTaken'; }
				elseif(
					$attempts->where('score', '<', $quiz->passing_criteria)->where('status', true)->count() >= $quiz->attempts
					|| $time_now > $quiz->end->format('Y-m-d H:i:s')
				) { $quiz_status = 'failed'; }
				elseif( $time_now < $quiz->start->format('Y-m-d H:i:s') ) { $quiz_status = 'notStartedYet'; }
				elseif( $quiz->in_progress == true ) { $quiz_status = 'inProgress'; }
				else { $quiz_status = 'available'; }

				$completion_percentage = 0;
				if( $quiz_status == 'inProgress' )
				{
					$in_progress = $attempts->where('status', false)->first();
					if( !empty( $in_progress ) )
					{
						$max_questions = $in_progress->quiz->questions->count();
						if( $max_questions > 0 )
						{
							$my_responses = $in_progress->responses->where('user_id', $user->id)->whereNotNull('response')->count();
							$completion_percentage = intval(($my_responses / $max_questions) * 100);
						}
					}
				}

				return view('admin/clients/quiz/overview', compact('quiz', 'quiz_status', 'completion_percentage', 'training', 'questions', 'tags', 'attempts', 'available', 'available_at', 'time_remaining', 'completion_percentage', 'allowActions', 'active_now'));
			} else {
				return abort(401);
			}
		} catch (\Throwable $e) {
			Log::error('Error in quizzesController@myQuiz: ' . $e->getMessage());
		}
	}


	public function myQuizResponses($id, $quizId, $resultId)
	{
		try {
			$user = auth()->user();
			QuizResults::refresh_my_attempts();
			if ($user->hasAnyRole(['Registered'])) {
				if (!$user->HaveCompany) {
					return redirect()->route('user.profile_view')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
				}
				if (!$user->company) {
					return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
				}

				$company = $user->company;
				$cycleData = $company->cycledata;

				if (empty($cycleData)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}
				if (empty($cycleData->organization_id)) {
					return redirect()->route('core.dashboard_view')->with(['msg' => 'You need to join program first.', 'class' => 'danger']);
				}

				$organization_id = $cycleData->organization_id;
				$quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail($quizId);
				$results = QuizResponses::where('quiz_id', $quizId)->where('user_id', $user->id)->where('result_id', $resultId)->with(['question'])->get();

				$training = $quiz->training;
				if (empty($training)) {
					return abort(404);
				}
				return view('admin/clients/quiz/results', compact('quiz', 'results', 'training'));
			} else {
				return abort(401);
			}
		} catch (\Throwable $e) {
			Log::error('Error in quizzesController@myQuizResponses: ' . $e->getMessage());
		}
	}

	public function quizResults(Request $request)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		if ($user->hasAnyRole(['Registered'])) {
			return abort(401);
		}

		$quizzesResults = DB::table('quiz_results AS qr')
			->select([
				'qr.id AS qr_result_id',
				'qr.user_id AS qr_id',
				'qr.quiz_id AS qr_quiz_id',
				'qr.score AS qr_result_score',
				'qr.status AS qr_result_status',
				'qr.started_at AS qr_result_started_at',
				'qr.ended_at AS qr_result_ended_at',
				'q.id AS q_quiz_id',
				'q.title AS q_quiz_title',
				'q.organization_id AS q_quiz_organization_id',
				'q.training_id AS q_quiz_training_id',
				'q.description AS q_quiz_description',
				'q.start AS q_quiz_start',
				'q.end AS q_quiz_end',
				'q.time_limit AS q_quiz_time_limit',
				'q.attempts AS q_quiz_attempts',
				'q.retry_after AS q_quiz_retry_after',
				'q.passing_criteria AS q_quiz_passing_criteria',
				'q.shuffle AS q_quiz_shuffle',
				'q.status AS q_quiz_status',
				'q.created_at AS q_quiz_created_at',
				'q.updated_at AS q_quiz_updated_at',
				't.id as t_training_id',
				't.title as t_training_title',
				't.dsc as t_training_description',
				'o.id as o_org_id',
				'o.name_ar as o_org_name_ar',
				'o.name_en as o_org_name_en',
				'u.id as u_user_id',
				'u.first_name_en as u_user_first_name_en',
				'u.first_name_ar as u_user_first_name_ar',
				'u.last_name_en as u_user_last_name_en',
				'u.last_name_ar as u_user_last_name_ar',
				'u.img_url as u_user_img_url',
				'c.id as c_company_id',
				'c.name_en as c_company_name_en',
				'c.name_ar as c_company_name_ar',
				'c.logo as c_company_logo',
			])
			->leftJoin('quizzes AS q', 'qr.quiz_id', '=', 'q.id')
			->leftJoin('trainings AS t', 't.id', '=', 'q.training_id')
			->leftJoin('organizations AS o', 'o.id', '=', 'q.organization_id')
			->leftJoin('users AS u', 'u.id', '=', 'qr.user_id')
			->leftJoin('companies AS c', 'c.id', '=', 'u.company_id')
			->when(!$user->hasRole('admin') && !empty($orgIds), function ($query) use ($orgIds) {
				$query->whereIn('q.organization_id', $orgIds);
			})
			->get();

		return view('admin.clients.quiz.quiz_results', compact('quizzesResults'));
	}
	public function quizResultsDetails($id)
	{
		$user = auth()->user();
		if ($user->hasAnyRole(['Registered'])) {
			return abort(401);
		}
		$quizzesResults_q = "SELECT 
		qr.id AS qr_result_id,
		qr.user_id AS qr_id,
		qr.quiz_id AS qr_quiz_id,
		qr.score AS qr_result_score,
		qr.status AS qr_result_status,
		qr.started_at AS qr_result_started_at,
		qr.ended_at AS qr_result_ended_at,
		q.id AS q_quiz_id,
		q.title AS q_quiz_title,
		q.organization_id AS q_quiz_organization_id,
		q.training_id AS q_quiz_training_id,
		q.description AS q_quiz_description,
		q.start AS q_quiz_start,
		q.end AS q_quiz_end,
		q.time_limit AS q_quiz_time_limit, 
		q.attempts AS q_quiz_attempts, 
		q.retry_after AS q_quiz_retry_after, 
		q.passing_criteria AS q_quiz_passing_criteria, 
		q.shuffle AS q_quiz_shuffle, 
		q.status AS q_quiz_status, 
		q.created_at AS q_quiz_created_at, 
		q.updated_at AS q_quiz_updated_at,
		t.id as t_training_id,
		t.title as t_training_title,
		t.dsc as t_training_description,
		o.id as o_org_id,
		o.name_ar as o_org_name_ar,
		o.name_en as o_org_name_en
			FROM quiz_results qr
			LEFT JOIN quizzes q ON qr.quiz_id = q.id
			LEFT JOIN trainings t ON t.id = q.training_id
			LEFT JOIN organizations o ON o.id = q.organization_id
			LEFT JOIN users u ON u.id = qr.user_id
			WHERE qr.id= ? ";

		$quizResult = collect(DB::select($quizzesResults_q, [$id]));
		
		return view('admin.clients.quiz.quiz_results_details', compact('quizResult'));

	}




}
