<?php

namespace App\Http\Controllers;


use App\Step;
use App\Tags;
use App\User;
use stdClass;
use App\Cycle;
use App\Company;
use App\Quizzes;
use App\Surveys;
use App\Training;
use App\Expertise;
use Carbon\Carbon;
use App\QuizResults;
use App\SurveyScopes;
use App\QuizResponses;
use App\SessionReport;
use http\Env\Response;
use App\TrainingContent;
use App\TrainingSession;
use App\SessionRecording;
use App\MentorshipRequest;
use App\TrainingCompleted;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\ExportTresultData;
use App\Traits\ZoomMeetingTrait;
use App\Services\WhatsAppService;
use App\AttendanceTrainingOffline;
use App\Notifications\ChangeCycle;
use Illuminate\Support\Facades\DB;
use App\Notifications\TrainerEmail;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendWhatsAppMessageJob;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TrainingResultExport;
use App\Factories\OnlineMeetingFactory;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendTrainingNotificationJob;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Notifications\NewTrainingNotification;
use Intervention\Image\ImageManagerStatic as Image;

class TrainingController extends Controller
{
	protected $meetingService;
	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->whatsAppService = $whatsAppService;
	}

	public function exportTresult(Request $request)
	{
		$course_id = $request->input('location', null);
		$step_id = $request->input('step', null);
		$user = auth()->user();
		// تحقق من أن البريد الإلكتروني موجود
	
		try {
			$courses = collect(DB::select("SELECT id FROM training_contents WHERE training_id = ?", [$course_id]));
			$course_contents = $courses->count();
			// 'cycle',
			// 'step',
			$inputs = [];
			$query = "SELECT
					com.id,
					com.name_en,
					com.name_ar,
					com.created_at,
					u.id AS user_id,
					u.first_name_en AS user_first_name_en,
					u.last_name_en AS user_last_name_en,
					u.first_name_ar AS user_first_name_ar,
					u.last_name_ar AS user_last_name_ar,
					c.id AS cycle_id,
					c.title AS cycle_title,
					s.id AS step_id,
					s.title AS step_title,
					CONCAT(TRIM(COALESCE(tc.training_id, '')), '_', TRIM(COALESCE(tc.user_id, '')), '_', TRIM(COALESCE(tc.course_id, ''))) AS training_completed_id
				FROM
					companies com
				LEFT JOIN
					users u ON u.company_id = com.id
				LEFT JOIN
					cycles c ON c.id = com.cycle
				LEFT JOIN
					steps s ON s.id = com.step
				LEFT JOIN
					training_completed tc ON tc.user_id = u.id AND tc.course_id = ?
				WHERE
					com.deleted_at IS NULL
					AND u.deleted_at IS NULL
					AND u.HaveCompany = 1
					AND c.deleted_at IS NULL
					AND s.deleted_at IS NULL
			";

			$inputs[] = $course_id;
			if( !$user->hasRole('Admin') )
			{
				$query.= " AND com.step = ?";
				$inputs[] = $step_id;
			}

			$l = app()->getLocale();
			$compaines = collect(DB::select($query, $inputs))->filter(function($company){
				return !is_null($company->cycle_id) && !is_null($company->step_id);
			})->map(function($c) {
				$c->training_completed_id = trim( $c->training_completed_id ) == '__' ? null : $c->training_completed_id;
				return $c;
			})->groupBy('id')->map(function($combinedComanies) use ($l, $course_contents) {
				$c = $combinedComanies->first();
				$company = new stdClass;
				$company->id = $c->id;
				$company->name = $c->{'name_' . $l};
				$company->created_at = $c->created_at;
				$company->users = $combinedComanies->map(function($u) use ($l) {
					$user = new stdClass;
					$user->id = $u->user_id;
					$user->name = trim( $u->{'user_first_name_' . $l} . ' ' . $u->{'user_last_name_' . $l} );
					return $user;
				})->unique('id')->values();
				$completed = $combinedComanies->whereNotNull('training_completed_id')->count();
				$company->program = new stdClass;
				$company->program->id = $c->cycle_id;
				$company->program->name = $c->cycle_title;
				$company->step = new stdClass;
				$company->step->id = $c->step_id;
				$company->step->name = $c->step_title;
				$company->completed = $course_contents > 0 ? ( round( ( $completed / $course_contents ) * 100, 2 ) ) : 0;
				return $company;
			})->values();

			return Excel::download(new TrainingResultExport($compaines), 'training_results_report.xlsx');
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@exportTresult: ' . $e->getMessage());
		}
	}

	public function changAllTL(Request $request)
	{
		try {
		$this->validate($request, [
			'checkget' => 'required|integer',
			'compid' => 'required|array',
			'compid.*' => 'required|integer',

		]);
		$checkget = $request->input('checkget');
		$compid = $request->input('compid');
		foreach ($compid as $index => $id)
		{
			$company = Company::where('id', $id)->first();
			$data['last_step'] = Step::findOrFail($checkget)->title;
			if ($checkget == 0) { $by = Auth::id(); }
			else
			{
				$cstep = Step::findOrFail($checkget);
				if ($cstep->arr > 1) {
					$approved = 1;
					$by = Auth::id();
				}
				$data['new_step'] = $cstep->title;
			}
			$update = $company->update(['step' => $checkget, 'approved' => $approved, 'approved_by' => $by]);
			if ($checkget != 0)
			{
				/// notify Company Owner via Mail
				$company_owner = $company->user;
				$data['owner_name_en'] = $company_owner->first_name_en . " " . $company_owner->last_name_en;
				$data['owner_name_ar'] = $company_owner->first_name_ar . " " . $company_owner->last_name_ar;
				$data['company_name_ar'] = $company_owner->company->name_ar;
				$data['company_name_en'] = $company_owner->company->name_en;
				$data['organization_name_ar'] = $cstep->cycle->organization->name_ar;
				$data['organization_name_en'] = $cstep->cycle->organization->name_en;

				$mail = new ChangeCycle($data);
				// $MSG = new TemplateEmail;
				// $MSG->Message=["Step changed - VI portal","Dear ".$rfullname.",","Your step into Cycle changed, check your account now.","/dashboard"];
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					try {
						$company_owner->notify($mail);
					} catch (\Throwable $th) {
						// throw $th;
					}
				}
			}
		}
		/// End of mail send
		if ($update) {
			return response()->json(['state' => 'success'], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
		return response()->json(['state' => 'success'], 200);
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@changAllTL: ' . $e->getMessage());
		}
	}

	public function all(Request $request, $id = 0)
	{
		try {
		$courses = Training::orderBy('created_at', 'DESC');
		if ($request->catTr_id) {
			$courses = $courses->whereHas('categories', function ($query) use ($request) {
				$query->whereIn('categories_id', (array) $request->catTr_id);
			})
			->where('status', 1)
			->orderBy('created_at', 'DESC');
		}
		if ($request->status) {
			if($request->status == "draft"){
				$courses = $courses->where('Status', 0);
			}else{
				$courses = $courses->where('Status', 1);
			}
		}
		$courses = $courses->get();
		if ($id == 0 && count($courses)) { $id = $courses->pluck('id')[0]; }
		$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

		return view('admin/settings/training/all', compact('courses', 'id', 'contents'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@all: ' . $e->getMessage());
		}
	}

	public function edit($id)
	{
		$courses = Training::findOrFail($id);
		return response()->json($courses);
	}

	public function store(Request $request)
	{
		try {
		if ($request->hasFile('image'))
		{
			$img_url = $request->file('image');
			$img_url = $img_url->store('training', ['disk' => 'public_uploads']);
			Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 350, function ($con) {
				$con->aspectRatio();
			})->save();

			$training = Training::updateOrCreate(
				['id' => $request->id],
				[
					'title' => $request->title,
					'dsc' => $request->dsc,
					'code' => $request->code,
					'CoursLevel' => $request->CoursLevel,
					'Quizs' => $request->Quizs,
					'Instractor' => $request->Instractor,
					'start_date' => $request->start_date,
					'end_date' => $request->end_date,
					'Status' => $request->Status,
					'user_id' => Auth::id(),
					'image' => $img_url
				]
			);

			$expertises = [];
			foreach ($request->CoursCategories as $expertiseId) {
				$expertises[] = [
					'categories_id' => $expertiseId,
					'training_id' => $training->id
				];
			}

			DB::table('categories_training')->where('training_id', $training->id)->delete();
			// إدراج الخبرات دفعة واحدة
			DB::table('categories_training')->insert($expertises);

			if( !empty( array_filter( $request->Tags ) ) )
			{
				$tags = [];
				foreach ($request->Tags as $tagName)
				{
					// تحقق مما إذا كان الـ tag موجود بالفعل في الجدول
					$tag = Tags::where('name', $tagName)->first();
					// إذا لم يكن موجودًا، قم بإنشائه
					if (!$tag) { $tag = Tags::create(['name' => $tagName]); }
					// قم بإضافة الـ tag_id و training_id إلى القائمة لإدراجها في الجدول الوسيط
					$tags[] = [
						'tag_id' => $tag->id,
						'training_id' => $training->id
					];
				}
				DB::table('tag_training')->where('training_id', $training->id)->delete();
				// إدراج الـ tags المرتبطة بالتدريب دفعة واحدة في الجدول الوسيط
				DB::table('tag_training')->insert($tags);
			}
		}
		else
		{
			$training = Training::updateOrCreate(
				['id' => $request->id],
				[
					'title' => $request->title,
					'dsc' => $request->dsc,
					'code' => $request->code,
					'CoursLevel' => $request->CoursLevel,
					'Quizs' => $request->Quizs,
					'Status' => $request->Status,
					'user_id' => Auth::id()

				]
			);
			$expertises = [];
			foreach ($request->CoursCategories as $expertiseId) {
				$expertises[] = [
					'categories_id' => $expertiseId,
					'training_id' => $training->id
				];
			}

			DB::table('categories_training')->where('training_id', $training->id)->delete();

			// إدراج الخبرات دفعة واحدة
			DB::table('categories_training')->insert($expertises);

			if( !empty( array_filter( $request->Tags ) ) )
			{
				$tags = [];
				foreach ($request->Tags as $tagName) {
					// تحقق مما إذا كان الـ tag موجود بالفعل في الجدول
					$tag = Tags::where('name', $tagName)->first();
					// إذا لم يكن موجودًا، قم بإنشائه
					if (!$tag) {
						$tag = Tags::create(['name' => $tagName]);
					}
					// قم بإضافة الـ tag_id و training_id إلى القائمة لإدراجها في الجدول الوسيط
					$tags[] = [
						'tag_id' => $tag->id,
						'training_id' => $training->id
					];
				}
	
				DB::table('tag_training')
					->where('training_id', $training->id)
					->delete();
				// إدراج الـ tags المرتبطة بالتدريب دفعة واحدة في الجدول الوسيط
				DB::table('tag_training')->insert($tags);
			}
		}

		return redirect('/dashboard/training/all/')->with(['class' => 'success', 'message' => 'Saved']);
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@store: ' . $e->getMessage());
		}
	}

	public function delete($id, Request $request)
	{
		try {
		if ($request)
		{
			Training::where('id', $id)->delete();
			TrainingContent::where('training_id', $id)->delete();
			DB::table('tag_training')->where('training_id', $id)->delete();
			DB::table('categories_training')->where('training_id', $id)->delete();
			return response()->json(['state' => 'success']);
		} else { return response()->json(['state' => 'failed'], 500); }
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@delete: ' . $e->getMessage());
		}
	}

	public function addcontent($id)
	{
		try {
		$check = Training::findOrFail($id);
		$arr = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get(['title', 'arr']);
		if (count($arr) == 0) { $arr = []; }
		if ($check) {
			return view('admin/settings/training/add_content', compact('id', 'arr'));
		} else {
			return back()->with(['class' => 'danger', 'message' => 'must add training course first.']);
		}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@addcontent: ' . $e->getMessage());
		}
	}

	public function editcontent($id)
	{
		try {
		$content = TrainingContent::findOrFail($id);
		$arr = TrainingContent::where('training_id', $content->training_id)->orderBy('arr', 'ASC')->get(['title', 'arr']);
		if ($content) {
			return view('admin/settings/training/edit_content', compact('content', 'id', 'arr'));
		} else {
			return back()->with(['class' => 'danger', 'message' => 'Error, content not exist.']);
		}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@editcontent: ' . $e->getMessage());
		}
	}

	public function viewcontent($id)
	{
		try {
		$content = TrainingContent::findOrFail($id);
		if ($content) {
			return view('admin/settings/training/view_content', compact('content'));
		} else {
			return back()->with(['class' => 'danger', 'message' => 'Error, content not exist.']);
		}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@viewcontent: ' . $e->getMessage());
		}
	}

	public function storecontent(Request $request)
	{
		try {
		if ($request->tcontent)
		{
			$id = $request->id ?? null;
			$arr = $i = 1;
			if ($request->arr) {
				$arr = $request->arr;
				$old = TrainingContent::where('training_id', $request->training_id)->orderBy('arr', 'ASC')->get();
				foreach ($old as $item) {
					if ($item->arr == $arr) {
						$i++;
					}
					TrainingContent::where('id', $item->id)->update(['arr' => $i++]);
				}
			}
			TrainingContent::updateOrCreate(['id' => $id], ['title' => $request->title, 'content' => $request->tcontent, 'training_id' => $request->training_id, 'user_id' => Auth::id(), 'arr' => $arr]);
			return redirect()->route('trainings.course_view', ['id' => $request->training_id])->with(['class' => 'success', 'message' => 'Saved']);
			// return redirect('/dashboard/training/all/' . $request->training_id)->with(['class' => 'success', 'message' => 'Saved']);
		}
		return back()->withInput()->with(['class' => 'danger', 'message' => 'Error']);

		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@storecontent: ' . $e->getMessage());
		}
	}

	public function delcontent($id, Request $request)
	{
		try{
		if ($request)
		{
			$removedid = TrainingContent::where('id', $id)->first(['training_id'])->training_id;
			TrainingContent::where('id', $id)->delete();
			/// Rearrange
			$old = TrainingContent::where('training_id', $removedid)->orderBy('arr', 'ASC')->get();
			foreach ($old as $key => $item) {
				TrainingContent::where('id', $item->id)->update(['arr' => $key + 1]);
			}
			return response()->json(['state' => 'success'], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@attendanceReport: ' . $e->getMessage());
		}
	}

	public function upload(Request $request)
	{
		$fileName = $request->file('file');
		$fileName = $fileName->store('photos', ['disk' => 'TrainingDisk']);
		return response()->json(['location' => '/dashboard/training/' . $fileName]); //env('APP_URL'). full URL
	}

	public function my($id, Request $request)
	{
		try {
			$step = optional(Auth::user()->company)->step;
			$tr = Step::findOrfail($step);
			$c = $request->c ?? 0;
			if ($tr->stype == 4) {
				$contents = TrainingContent::where('training_id', $id)->orderBy('arr')->get(['id', 'title', 'training_id']);
				$ccontent = TrainingContent::where('training_id', $id);
				$ccontent = $c == 0 ? $ccontent->orderBy('arr', 'ASC') : $ccontent->where('id', $c);
				$ccontent = $ccontent->first(['id', 'title', 'content', 'created_at']);

				$training = "SELECT *,
				Concat(u.first_name_en, ' ', u.last_name_en) AS name_en,
				Concat(u.first_name_ar, ' ', u.last_name_ar) AS name_ar
				FROM trainings AS t 
				LEFT JOIN users AS u ON t.user_id = u.id
				WHERE t.id = ? AND t.deleted_at IS NULL";
				$training = DB::select($training, [$id]);
				$training = $training[0];

				if (count($contents) == 0) {
					return back()->with(['class' => 'danger', 'message' => 'Error, content not exist.']);
				}
				// save progress
				TrainingCompleted::updateOrInsert(['training_id' => $ccontent->id, 'user_id' => Auth::id()], ['course_id' => $id]);

				$completedCount = TrainingCompleted::where('course_id', $id)
				->where('user_id', Auth::id())
				->count();

				$totalContent = $contents->count();
				$progressPercentage = $totalContent > 0 ? round(($completedCount / $totalContent) * 100) : 0;


				$quizzes = Quizzes::where('training_id', $id)->where('status', 'published')->get();
				return view('admin/clients/training', compact('contents', 'ccontent', 'quizzes', 'progressPercentage', 'training'));
			} else {
				return back()->with(['class' => 'danger', 'message' => 'ErrorNoPermissions']);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@my: ' . $e->getMessage());
		}
	}

	public function myQuiz($id, $quizId, Request $request)
	{
		try{
			$user = auth()->user();
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
				return view('admin/clients/quiz/overview', compact('quiz', 'training', 'questions', 'tags', 'attempts', 'available', 'available_at', 'time_remaining', 'completion_percentage', 'allowActions', 'active_now'));
			} else {
				return abort(401);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@myQuiz: ' . $e->getMessage());
		}
	}

	public function doQuiz($id, $quizId, Request $request)
	{
		try{
			$user = auth()->user();
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
					QuizResponses::insert($responses);
					$responses = QuizResponses::where('quiz_id', $quiz->id)->where('user_id', $user->id)->where('result_id', $result->id)->get();
				}
				$time_limit = $result->started_at->copy()->addMinutes($quiz->time_limit);
				$remaining = $time_limit->diffInSeconds(now());

				if ($remaining > 0) {
					$response = $responses->whereNull('response')->first();
					if (empty($response)) {
						$answers = QuizResponses::where('quiz_id', $quizId)->where('user_id', $user->id)->where('result_id', $result->id)->get();
						$score = $answers->where('status', true)->count();
						$result->score = round(floatval($score / $answers->count()), 2) * 100;
						$result->status = true;
						$result->ended_at = now()->format('Y-m-d H:i:s');
						$result->save();
						return redirect()->route('quizzes.view_overview_mine', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizCompleted'), 'class' => 'success']);
					}
					$question = $response->question;

					$diff = now()->diff($time_limit);
					$time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
					return view('admin/clients/quiz/taking', compact('quiz', 'training', 'questions', 'question', 'response', 'time_remaining'));
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
			Log::error('Error in TrainingController@doQuiz: ' . $e->getMessage());
		}
	}

	public function myQuizResponses($id, $quizId, $resultId)
	{
		try{
		$user = auth()->user();
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
			Log::error('Error in TrainingController@myQuizResponses: ' . $e->getMessage());
		}
	}

	public function results($id = 0)
	{
		try{
			$organization_id = auth()->user()->organization_id;
			$sessions = TrainingSession::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('step', function ($query) use ($organization_id) {
					$query->whereHas('cycle', function ($qer) use ($organization_id) {
						$qer->where('organization_id', $organization_id);
					});
				});
			})->where('type', 'online')->orderBy('datetime')->get();
			if ($id == 0) {
				$id = $sessions->pluck('id')->first();
				$csession = TrainingSession::where('type', 'online')->where('id', $id)->first();
			} else {
				$csession = TrainingSession::where('type', 'online')->where('id', $id)->first();
			}
			$contents = 0;
			
			if ($csession) {
				$contents = TrainingContent::where('training_id', $csession->training_Address)->count();
			}
			$steps11 = Step::where('id', $csession->step_id)->orderBy('arr')->get();
			foreach ($steps11 as $steps1) {
				$companies = Company::where('cycle', $steps1->cycle_id)->get();
			}
			$uniqueCycleIds = $companies->pluck('cycle')->unique();
			$steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();
			

			return view('admin.settings.training.results', compact('csession', 'sessions', 'id', 'contents', 'steps'));
		}catch (\Throwable $e) {
			Log::error('Error in TrainingController@myQuiz: ' . $e->getMessage());
		}
	}
	public function sessions(Request $request, $id = 0)
	{
		try{
			$organization_id = auth()->user()->organization_id;
			$steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('stype', 4)->get();
			if ($id == 0) {
				$id = $steps->pluck('id')->first();
			}
			$sessions = TrainingSession::where('step_id', $id)->with('reports')->orderBy('datetime', 'ASC');
			$online = Training::get();
			if($request->type != 0){
				$sessions = $sessions->where('type', $request->type);
			}
			if ($request->dateFrom != 0) {
				$sessions = $sessions->where('datetime', '>=', $request->dateFrom);
			}
			
			if ($request->dateTo != 0) {
				$sessions = $sessions->where('datetime', '<=', $request->dateTo);
			}

			$sessions = $sessions->get();
			// dd($sessions->toArray());
			return view('admin/settings/training/sessions', compact('steps', 'id', 'sessions', 'online'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessions: ' . $e->getMessage());
		}
	}

	public function mysessions()
	{
		try{
			$sessions = [];
			if (Auth::user()->company) {
				$sessions = TrainingSession::where('step_id', Auth::user()->company->step)->orderBy('datetime', 'DESC')->get();
			}
			return view('admin/settings/training/mysessions', compact('sessions'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@mysessions: ' . $e->getMessage());
		}
	}

	public function addsession(Request $request)
	{
		try{
			$addOrEdit = 'add';
			$this->meetingService =  OnlineMeetingFactory::create();
			
			$dateRange = $request->date_range; 
			$type = $request->type;
			$l = app()->getLocale();
			if ($type == 'sessions' || $type == 'offline')
			{
				$startDate = $request->datetime;
				$startTime = $request->starttime;   // وقت البداية
				$endTime = $request->endtime;   // وقت النهاية
				$start_datetime = strtotime($startDate.' '. $startTime);
				$end_datetime = strtotime($startDate.' '.$endTime);
			}
			else if ($type == 'online')
			{
				[$startDate, $endDate] = explode(' - ', $dateRange);
				$start_datetime = strtotime($startDate) ;
				$end_datetime = strtotime($endDate);
			}
			
			if($start_datetime == null || $end_datetime == null){
				return back()->with(['message' => 'Please fill all fields', 'class' => 'danger']);
			}

			$step = $request->step_id;
			$title = $request->title;
			$dsc = $request->dsc;
			$datetime = $start_datetime;
			$datetimeStamp = Carbon::parse($datetime)->timestamp;
			$end_datetimeStamp = Carbon::parse($end_datetime)->timestamp;
			$start = Carbon::parse($datetime);
			$end = Carbon::parse($end_datetime);
			$duration = $start->diffInMinutes($end);

			if ($type == 'sessions') {
				$trainingSession = $this->addorcreatSession($type, $datetime, $request->duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->trainer_name, $request->trainer_email);
				// ======> notify Trainer <=================
				SendTrainingNotificationJob::dispatch($step, $trainingSession->id, $title, $datetime, $trainingSession->start_url, null, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email,$addOrEdit);
				// ==========> End Notify <================
				return back()->with(['message' => 'Session added successfully', 'class' => 'success']);

			} elseif ($type == 'online') {
					$addorcreatOnlineSession = $this->addorcreatOnlineSession($type, $datetime, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->online);
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					SendTrainingNotificationJob::dispatch($step, $addorcreatOnlineSession, $title, $datetime, null, null, $type, app()->getLocale(), null, null,$addOrEdit);	
				}
				return back()->with(['message' => 'Saved', 'class' => 'success']);

			} elseif ($type == 'offline') {
				$trainingSession = $this->addorcreatOfflineSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->training_Address, $request->training_location, $request->trainer_name, $request->trainer_email);
				// ======> notify Trainer <=================
				$join_url = $request->training_location ?? 'No Location';
				$address  = $request->training_Address ?? 'No Address';
				SendTrainingNotificationJob::dispatch($step, $trainingSession, $title, $datetime, $join_url, $address, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email,$addOrEdit);
				// ==========> End Notify <================
				return back()->with(['message' => 'Saved', 'class' => 'success']);
				
			} else {
				return back()->with(['message' => 'Can\'t add session', 'class' => 'danger']);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@addsession: ' . $e->getMessage());
		}
	}

	public function editsession(Request $request , $id)
	{
		try{
			// dd($request->all());
			$this->meetingService =  OnlineMeetingFactory::create();
			$addOrEdit = 'edit';
			$type = $request->type;
			$start_datetime = null;
			$end_datetime = null;
			$l = app()->getLocale();
			
			if ($type == 'sessions' || $type == 'offline') {
			$startDate = $request->datetime;
			$startTime = date('H:i', strtotime($request->starttime));
			$endTime = date('H:i', strtotime($request->endtime));
			$start_datetime = $startDate . ' ' . $startTime;
			$end_datetime = $startDate . ' ' . $endTime;
		
		}
		elseif ($type == 'online')
		{
			if (empty($request->date_range)) {
				return back()->with(['message' => 'Date range is required for online sessions', 'class' => 'danger']);
			}
			[$startDate, $endDate] = explode(' - ', $request->date_range);
			try {
				$start_datetime = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
				$end_datetime = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
			} catch (\Exception $e) {
				return back()->with(['message' => 'Invalid date format in date range', 'class' => 'danger']);
			}
		}

		if (is_null($start_datetime) || is_null($end_datetime)) {
			return back()->with(['message' => 'Please fill all required date and time fields', 'class' => 'danger']);
		}

		$step = $request->step_id;
		$title = $request->title;
		$dsc = $request->dsc;
		$datetime = $start_datetime;
		$datetimeStamp = Carbon::parse($datetime)->timestamp;
		$end_datetimeStamp = Carbon::parse($end_datetime)->timestamp;
		$start = Carbon::parse($datetime);
		$end = Carbon::parse($end_datetime);
		$duration = $start->diffInMinutes($end);
		// $step = $request->step_id;
		$title = $request->title;
		$dsc = $request->dsc;
		// $id = $request->sid;
		
		if ($type == 'sessions')
		{
			// Add Zoom
			$trainingSession = $this->addorcreatSession($type, $datetime, $request->duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->trainer_name, $request->trainer_email, $request->sid);
			if ($trainingSession instanceof \Illuminate\Http\RedirectResponse) {
				return $trainingSession; 
			}
			// $session = TrainingSession::find($trainingSession->sid);
			// ======> notify Trainer <=================
			SendTrainingNotificationJob::dispatch($step, $trainingSession->id, $title, $datetime, $trainingSession->start_url, null, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email,$addOrEdit);
			// ==========> End Notify <================
			return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);

		} elseif ($type == 'online') {
			$addorcreatOnlineSession = $this->addorcreatOnlineSession($type, $datetime, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->online, $request->sid);
			SendTrainingNotificationJob::dispatch($step, $addorcreatOnlineSession, $title, $datetime, null, null, $type, app()->getLocale(), null, null,$addOrEdit);
			return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);

		} elseif ($type == 'offline') {
			$trainingSession = $this->addorcreatOfflineSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->training_Address, $request->training_location, $request->trainer_name, $request->trainer_email, $request->sid);
			// ======> notify Trainer <=================
			$join_url = $request->training_location ?? 'No Location';
			$address = $request->training_Address ?? 'No Address';
			SendTrainingNotificationJob::dispatch($step, $trainingSession, $title, $datetime, $join_url, $address, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email,$addOrEdit);
			// ==========> End Notify <================
			return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);
		} else {
			return back()->with(['message' => 'Can\'t edit session', 'class' => 'danger']);
		}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@editsession: ' . $e->getMessage());
		}
	}

	private function addorcreatSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $trainer_name, $trainer_email, $sid = null)
	{
		$this->meetingService =  OnlineMeetingFactory::create();
		if ($type == 'sessions')
		{
			// Add Zoom
			$topic = "Training (" . $title . ')';
			$data = ['topic' => $topic, "start_time" => $datetime, 'duration' => $duration, 'host_video' => 1, 'participant_video' => 1];
			$onlineMeeting = $this->meetingService->createMeeting($data);
			if (!$onlineMeeting['success']) {
				return back()->with([
					'message' => $onlineMeeting['data'],
					'class' => 'danger'
				]);
			}
			$zoomid =  $onlineMeeting['data']['id'];
			$start = $onlineMeeting['data']['start_url'];
			$join = $onlineMeeting['data']['join_url'];
			
			$data = [
				'type' => $type,
				'step_id' => $step,
				'title' => $title,
				'dsc' => $dsc,
				'zoom_id' => $zoomid,
				'datetime' => $datetimeStamp,
				'enddatetime' => $end_datetimeStamp,
				'duration' => $duration,
				'user_id' => Auth::id(),
				'start_url' => $start,
				'join_url' => $join,
				'trainer_name' => $trainer_name,
				'trainer_email' => $trainer_email
			];
			if ($addOrEdit == 'add') {
				$trainingSession = TrainingSession::create($data);
			} else {
				$trainingSession_update = TrainingSession::where('id', $sid)->update($data);
				$trainingSession = TrainingSession::find($sid);
			}
			return $trainingSession;
		}
	}
	private function addorcreatOnlineSession($type, $datetime, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $online, $sid = null)
	{
		if (!$online) {
			return back()->with(['message2', 'Can\'t add training you must add new training course first']);
		}
		$data = [
			'type' => $type,
			'step_id' => $step, 
			'title' => $title, 
			'dsc' => $dsc, 
			'datetime' => $datetimeStamp, 
			'enddatetime' => $end_datetimeStamp, 
			'user_id' => Auth::id(), 
			'training_Address' => $online
		];
		if ($addOrEdit == 'add') {
			$trainingOnlineSession = TrainingSession::create($data);
		} else {
			$trainingSession_update = TrainingSession::where('id', $sid)->update($data);
			$trainingOnlineSession = TrainingSession::find($sid);
		}

		return $trainingOnlineSession;
	}

	private function addorcreatOfflineSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $training_Address, $training_location, $trainer_name, $trainer_email, $sid = null)
	{
		$data = [
			'type' => $type,
			'step_id' => $step,
			'title' => $title,
			'dsc' => $dsc,
			'datetime' => $datetimeStamp,
			'user_id' => Auth::id(),
			'training_Address' => $training_Address,
			'training_location' => $training_location,
			'enddatetime' => $end_datetimeStamp,
			'trainer_name' => $trainer_name,
			'trainer_email' => $trainer_email
		];
		if ($addOrEdit == 'add') {
			$trainingSession = TrainingSession::create($data);
		} else {
			$trainingSession_update = TrainingSession::where('id', $sid)->update($data);
			$trainingSession = TrainingSession::find($sid);
		}
		// رابط المسح الضوئي للحضور
		$attendanceUrl = url("/dashboard/training/session-attendance-offline/{$trainingSession->id}");
		// اسم ملف QR Code
		$qrFileName = 'training_' . $trainingSession->id . '.png';
		// توليد QR Code كـ string
		$qrCode = QrCode::format('png')->size(300)->generate($attendanceUrl);
		// المسار الكامل داخل `public/`
		$qrFilePath = '/qrcodes/' . $qrFileName;
		// حفظ QR Code في `public/qrcodes/`
		$add = Storage::disk('QrCodePublic')->put($qrFileName, $qrCode);

		// تحديث الحقل `QrCode` في الجلسة التدريبية (يجب حفظ اسم الملف فقط)
		$trainingSession->update(['QrCode' => $qrFilePath]);

		return $trainingSession;
	}

	public function session(Request $request)
	{
		if ($request->id)
		{
			$sessionData = TrainingSession::findOrFail($request->id);
			$session = [];
			$session['id'] = $sessionData->id;
			$session['title'] = $sessionData->title;
			$session['dsc'] = $sessionData->dsc;
			$session['type'] = $sessionData->type;
			if($sessionData->type == 'online'){
				$session['daterange'] = FormatDate($sessionData->datetime, 'Y-m-d').' - '.FormatDate($sessionData->enddatetime, 'Y-m-d');
			}else{
				$session['daterange'] = FormatDate($sessionData->datetime, 'Y-m-d');
				$session['fromtime'] = FormatDate($sessionData->datetime, 'h:i A');
				$session['endtime'] = FormatDate($sessionData->enddatetime, 'h:i A');
			}
			if($sessionData->type == 'sessions'){
				$session['training_Address'] = $sessionData->training_Address;
			}elseif($sessionData->type == 'offline'){
				$session['training_Address'] = $sessionData->training_Address;
				$session['training_location'] = $sessionData->training_location;
			}elseif($sessionData->type == 'online'){
				$session['course_id'] = $sessionData->training_Address;
			}
			$session['trainer_name'] = $sessionData->trainer_name;
			$session['trainer_email'] = $sessionData->trainer_email;
			$session['QrCode'] = $sessionData->QrCode;
			$session['duration'] = $sessionData->duration;
			$session['start_url'] = $sessionData->start_url;
			$session['join_url'] = $sessionData->join_url;
			$session['created_at'] = $sessionData->created_at;
			$session['step_id'] = $sessionData->step_id;

			return response()->json($session);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function getattendanceOffline(Request $request)
	{
		if ($request->id)
		{
			$attendanceData = AttendanceTrainingOffline::where('training_session_id',$request->id)->with('user', 'trainingSession', 'step', 'cycle','company')->get();
			// return response()->json([
			// 	'success' => true,
			// 	'message' => 'تم تسجيل الحضور بنجاح',
			// 	'attendances' => $attendanceData,
			// ]);
			return view('admin.settings.training.attendance', compact('attendanceData'));
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function QrCodeTraining(Request $request)
	{
		try
		{
			if ($request->id)
			{
				$session = DB::table('training_sessions')->where('id', $request->id)->first();
				
				// رابط المسح الضوئي للحضور
				$attendanceUrl = url("/dashboard/training/session-attendance-offline/{$session->id}");
				// اسم ملف QR Code
				$qrFileName = 'training_' . $session->id . '.png';
				// توليد QR Code كـ string
				$qrCode = QrCode::format('png')->size(300)->generate($attendanceUrl);
				// المسار الكامل داخل `public/`
				$qrFilePath = '/qrcodes/' . $qrFileName;
				
				// حفظ QR Code في `public/qrcodes/`
				$add = Storage::disk('QrCodePublic')->put($qrFileName, $qrCode);
				
				// تحديث الحقل `QrCode` في الجلسة التدريبية (يجب حفظ اسم الملف فقط)
				$session = DB::table('training_sessions')
							->where('id', $request->id)
							->update(['QrCode' => $qrFilePath]);

				return response()->json(['massege' => 'Created Success']);
			} else {
				return response()->json(['state' => 'failed'], 500);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@QrCodeTraining: ' . $e->getMessage());
		}
	}

	public function delsession($id, Request $request)
	{
		try
		{
			// $zoomid = TrainingSession::findOrFail($id)->zoom_id;
			if ($request)
			{
				// if ($zoomid) {
				// 	$zoom = $this->delete($zoomid);
				// 	if (!$zoom['success']) {
				// 		return response()->json(['state' => 'failed'], 500);
				// 	}
				// }
				TrainingSession::findOrFail($id)->delete();
				return response()->json(['state' => 'success'], 200);
			} else {
				return response()->json(['state' => 'failed'], 500);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@delsession: ' . $e->getMessage());
		}
	}

	public function sessionReport(Request $request, $id)
	{
		$this->meetingService =  OnlineMeetingFactory::create();
		try
		{
	 		$type = $request->query('type');
			$zoomId = null;
			$session = null;
			$mentorshipRequest = null;

			if ($type === 'training') {
				$session = TrainingSession::findOrFail($id);
				$zoomId = $session->zoom_id;
			} elseif ($type === 'mentorship') {
				$mentorshipRequest = MentorshipRequest::findOrFail($id);
				$zoomId = $mentorshipRequest->zoom_id;
			}
			if (!$this->meetingService || !method_exists($this->meetingService, 'getSessionReport')) {
				return redirect()->back()->with(['message2' => 'session_report_not_available']);
			}

			$response = $this->meetingService->getSessionReport($zoomId);
			if ($response['success'])
			{
				$participants = $response['data']['participants'] ?? [];

				foreach ($participants as $participant) {
					$joinDate = isset($participant['join_time'])
						? Carbon::parse($participant['join_time'])->toDateTimeString()
						: null;
					$endDate = isset($participant['leave_time'])
						? Carbon::parse($participant['leave_time'])->toDateTimeString()
						: null;

					SessionReport::updateOrCreate(
						[
							'session_id' => $session ? $session->id : null,
							'participant_user_id' => $participant['user_id'] ?? null,
						],
						[
							'mentorship_request_id' => $mentorshipRequest ? $mentorshipRequest->id : null,
							'participant_email' => $participant['user_email'] ?? null,
							'participant_name' => $participant['name'] ?? null,
							'participant_join_date' => $joinDate,
							'participant_end_date' => $endDate,
							'attendance_duration' => $participant['duration'] ?? 0,
						]
					);
				}
				return redirect()->back()->with(['message' => 'Session report updated', 'class' => 'success']);
			} else {
				return redirect()->back()->with(['message2' => 'session report is not available yet']);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessionReport: ' . $e->getMessage());
			return redirect()->back()->with(['message2' => 'error_updating_report' . ': ' . $e->getMessage()]);
		}
	}

	public function sessionRecord($id)
	{
		$this->meetingService =  OnlineMeetingFactory::create();
		try
		{
			$sessionId = null;
			$zoomId = null;
			$mentorshipRequest = null;

			$type = request()->query('type');

			if ($type === 'training') {
				$session = TrainingSession::findOrFail($id);
				$zoomId = $session->zoom_id;
				$sessionId = $session->id;
			} elseif ($type === 'mentorship') {
				$mentorshipRequest = MentorshipRequest::findOrFail($id);
				$zoomId = $mentorshipRequest->zoom_id;
			}
			
			if (!$this->meetingService || !method_exists($this->meetingService, 'getSessionRecord')) {
				return redirect()->back()->with(['message2' => 'Session Recording is not available yet']);
			}

			$response = $this->meetingService->getSessionRecord($zoomId);

			$recordingFiles = $response['data']['recording_files'] ?? [];
			$password = $response['data']['password'] ?? null;

			$firstFile = $recordingFiles[0] ?? null;

			if($response['success'])
			{
				$recordingTime = isset($firstFile['recording_start'])
					? Carbon::parse($firstFile['recording_start'])->toDateTimeString()
					: null;

				SessionRecording::updateOrCreate(
					[
						'recording_id' => $firstFile['id'],
					],
					[
						'session_id' => $sessionId ? $sessionId : null,
						'mentorship_request_id' => $mentorshipRequest ? $mentorshipRequest->id : null,
						'recording_type' => $firstFile['recording_type'] ?? null,
						'recording_view_url' => $firstFile['play_url'] ?? null,
						'recording_download_url' => $firstFile['download_url'] ?? null,
						'recording_time' => $recordingTime,
						'passcode' => $password,
					]
				);
				return redirect()->back()->with(['message' => 'Session Record updated', 'class' => 'success']);
			} else {
				return redirect()->back()->with(['message2' => 'Session Recording is not available yet']);
			}

		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessionRecord: ' . $e->getMessage());
			return redirect()->back()->with(['message2' => 'error_updating_recordings' . ': ' . $e->getMessage()]);
		}
	}

	public function attendanceReport(Request $request, $id)
	{
		try
		{
			$session = TrainingSession::find($id);

			if (!$session || !$session->zoom_id) {
				return response()->json(['success' => false, 'message' => 'Session not found'], 404);
			}

			if ($this->meetingService instanceof \App\Services\TeamsService) {
				$sessionReportResponse = $this->meetingService->getSessionReport($session->zoom_id);

				if (!$sessionReportResponse['success']) {
					return response()->json([
						'success' => false,
						'message' => 'Failed to fetch session reports: ' . $sessionReportResponse['message'],
					], 500);
				}

				$reports = $sessionReportResponse['data']['value'] ?? [];
				if (empty($reports)) {
					return response()->json([
						'success' => false,
						'message' => 'No attendance reports found for this session.',
					], 404);
				}

				$reportId = $reports[0]['id'] ?? null;

				if (!$reportId) {
					return response()->json([
						'success' => false,
						'message' => 'Unable to Determine the Report ID.',
					], 500);
				}

				$attendanceReportResponse = $this->meetingService->getMeetingAttendanceReport($session->zoom_id, $reportId);

				// dd($attendanceReportResponse);
				if ($attendanceReportResponse['success']) {
					return response()->json([
						'success' => true,
						'data' => $attendanceReportResponse['data'],
					], 200);
				}

				return response()->json([
					'success' => false,
					'message' => 'Failed to fetch attendance report: ' . $attendanceReportResponse['message'],
				], 500);
			}

			return response()->json([
				'success' => false,
				'message' => 'Invalid meeting service instance.',
			], 500);
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@attendanceReport: ' . $e->getMessage());
		}
	}

	public function getdsc($id)
	{
		if ($id) {
			$session = TrainingSession::findOrFail($id);
			return response()->json($session);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function create($id = 0)
	{
		try
		{
			$courses = Training::orderBy('created_at', 'DESC')->get();
			if ($id == 0 && count($courses)) {
				$id = $courses->pluck('id')[0];
			}
			$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

			$client = Auth::user();
			$randomNumber = sprintf('%06d', rand(0, 999999));

			$expertises = Expertise::where('parent_id', null)->get();
			$Quizzes = Quizzes::all();

			$Tags = Tags::all();

			// $instructors = User::whereHas('roles', function ($query) {
			// 	$query->where('name', 'Mentor');
			// })->get();

			return view('admin/settings/training/newCours', compact('randomNumber', 'Tags', 'expertises', 'client', 'Quizzes'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@create: ' . $e->getMessage());
		}
	}

	public function list_surveys($id, Request $request)
	{
		try
		{
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
		try
		{
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
		try
		{
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
		try
		{
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
		try
		{
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
		try
		{
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

	public function editTR($id)
	{
		try
		{
			$course = Training::where('id', $id)->orderBy('created_at', 'DESC')->first();
			if ($id == 0 && count($course)) {
				$id = $course->pluck('id')[0];
			}
			$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

			$expertises = Expertise::where('parent_id', null)->get();
			$Quizzes = Quizzes::all();

			$instructors = User::whereHas('roles', function ($query) {
				$query->where('name', 'Trainer');
			})->get();
			$training = Training::find($id);

			$tags = $training->tags;
			return view('admin/settings/training/editCours', compact('course', 'id', 'contents', 'expertises', 'Quizzes', 'instructors', 'tags'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@editTR: ' . $e->getMessage());
		}
	}

	public function showCourse($id)
	{
		try
		{
			$course = Training::where('id', $id)->orderBy('created_at', 'DESC')->first();
			if ($id == 0 && count($course)) {
				$id = $course->pluck('id')[0];
			}
			$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

			$expertises = Expertise::where('parent_id', null)->get();
			$Quizzes = Quizzes::all();


			$instructors = User::whereHas('roles', function ($query) {
				$query->where('name', 'Trainer');
			})->get();
			$training = Training::find($id);

			$tags = $training->tags;
			return view('admin/settings/training/viewCourse', compact('course', 'id', 'contents', 'expertises', 'Quizzes', 'instructors', 'tags'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@showCourse: ' . $e->getMessage());
		}
	}

	public function attendanceOffline(Request $request , $id)
	{
		try
		{
			$session = TrainingSession::find($id);
			$step_Traning = $session->step_id;
			$cycle_Traning = $session->step->cycle->id;
			$user = auth()->user();
			$userId = $user->id;
			$companyId = $user->company_id;
			$cycle_user = $user->company->cycle;
			$step_user = $user->company->step;
		
			if( $step_user == $step_Traning )
			{
				if($session->datetime > now()){
					return response()->json(['message' => 'WaitForStartAttendece', 'Start' => $session->datetime]);
				}
				if($session->enddatetime < now()){
					return response()->json(['message' => 'SessionEnded', 'Ended' => $session->enddatetime]);
				}
				$insert = DB::table('attendance_training_offline')
					->where('training_session_id', $session->id)
					->where('user_id', $userId)->first();
				if(!empty($insert))
				{
					$insert = DB::table('attendance_training_offline')
							->where('id', $insert->id)
							->update([
								'user_id' => $userId,
								'company_id' => $companyId,
								'training_session_id' => $session->id,
								'step_id' => $step_Traning,
								'cycle_id' => $cycle_Traning,
								'attendance' => 1,
								'location' => 'location',
								'created_at' => now(),
								'updated_at' => now(),
							]);
				}
				else
				{
					$insert = DB::table('attendance_training_offline')->insert([
						'user_id' => $userId,
						'company_id' => $companyId,
						'training_session_id' => $session->id,
						'step_id' => $step_Traning,
						'cycle_id' => $cycle_Traning,
						'attendance' => 1,
						'location' => 'location',
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
				return response()->json(['message' => 'youareRegisterAttendece']);
			}
			return response()->json(['message' => 'youareNotRegisterAttendece']);
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@attendanceOffline: ' . $e->getMessage());
		}
	}
}