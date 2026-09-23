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
use App\SessionAttendance;
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
use App\TrainingDuration;
use App\Services\MeetingWindowService;
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
			if (!$user->hasRole('Admin')) {
				$query .= " AND com.step = ?";
				$inputs[] = $step_id;
			}

			$l = app()->getLocale();
			$compaines = collect(DB::select($query, $inputs))->filter(function ($company) {
				return !is_null($company->cycle_id) && !is_null($company->step_id);
			})->map(function ($c) {
				$c->training_completed_id = trim($c->training_completed_id) == '__' ? null : $c->training_completed_id;
				return $c;
			})->groupBy('id')->map(function ($combinedComanies) use ($l, $course_contents) {
				$c = $combinedComanies->first();
				$company = new stdClass;
				$company->id = $c->id;
				$company->name = $c->{'name_' . $l};
				$company->created_at = $c->created_at;
				$company->users = $combinedComanies->map(function ($u) use ($l) {
					$user = new stdClass;
					$user->id = $u->user_id;
					$user->name = trim($u->{'user_first_name_' . $l} . ' ' . $u->{'user_last_name_' . $l});
					return $user;
				})->unique('id')->values();
				$completed = $combinedComanies->whereNotNull('training_completed_id')->count();
				$company->program = new stdClass;
				$company->program->id = $c->cycle_id;
				$company->program->name = $c->cycle_title;
				$company->step = new stdClass;
				$company->step->id = $c->step_id;
				$company->step->name = $c->step_title;
				$company->completed = $course_contents > 0 ? (round(($completed / $course_contents) * 100, 2)) : 0;
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
			$cstep = Step::with(['cycle'])->findOrFail($checkget) ?? null;
			$status = $checkget != 0 ? 'in' : 'out';
			foreach ($compid as $index => $id)
			{
				$company = Company::where('id', $id)->first();
				$data['new_step'] = '-';
				$data['last_step'] = Step::findOrFail($company?->step)?->title ?? '-';
				if ($checkget == 0) {
					$by = Auth::id();
				} else {
					// $cstep = Step::findOrFail($checkget);
					if ($cstep->arr > 1) {
						$approved = 1;
						$by = Auth::id();
					}
					$data['new_step'] = $cstep->title;
				}
				$update = $company->update(['step' => $checkget, 'approved' => $approved, 'approved_by' => $by]);
				// if ($checkget != 0) {
					/// notify Company Owner via Mail

					$start_date = '-';
					if( $cstep && $cstep->from ) { $start_date = Carbon::parse($cstep->from)->format('Y-m-d'); }
					$company_owner = $company->user;
					$data['owner_name_en'] = $company_owner->first_name_en . " " . $company_owner->last_name_en;
					$data['owner_name_ar'] = $company_owner->first_name_ar . " " . $company_owner->last_name_ar;
					$data['company_name_ar'] = $company_owner->company->name_ar;
					$data['company_name_en'] = $company_owner->company->name_en;
					$data['organization_name_ar'] = $cstep->cycle->organization->name_ar;
					$data['organization_name_en'] = $cstep->cycle->organization->name_en;
					$data['start_date'] = $start_date;
					$data['preferred_language'] = $company_owner->language;
					$data['program'] = $cstep->cycle;
					$data['status'] = $status;

					$mail = new ChangeCycle($data);
					// $MSG = new TemplateEmail;
					// $MSG->Message=["Step changed - VI portal","Dear ".$rfullname.",","Your step into Cycle changed, check your account now.","/dashboard"];
					if (!in_array(env('APP_ENV', 'local'), ['local']) || env('APP_NAME', 'incubator') != "BIAC") {
						try {
							$company_owner->notify($mail);
						} catch (\Throwable $th) {
							// throw $th;
						}
					}
				// }
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
			$courses = Training::visibleToUser()->with('organization')->orderBy('created_at', 'DESC');
			if( $request->catTr_id )
			{
				$courses = $courses->whereHas('categories', function ($query) use ($request) {
					$query->whereIn('categories_id', (array) $request->catTr_id);
				})
				->where('status', 1)
				->orderBy('created_at', 'DESC');
			}
			if( $request->status ) { $courses = $courses->where('Status', $request->status == "draft" ? 0 : 1); }

			$courses = $courses->get();
			if ($id == 0 && count($courses)) {
				$id = $courses->pluck('id')[0];
			}
			$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

			return view('admin/settings/training/all', compact('courses', 'id', 'contents'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@all: ' . $e->getMessage());
		}
	}

	public function edit($id)
	{
		$courses = Training::visibleToUser()->findOrFail($id);
		return response()->json($courses);
	}

	public function store(Request $request)
	{
		try {
			$user = auth()->user();

			// Determine organization_id
			$organizationId = null;
			if ($user->ableTo('trainings.set_public')) {
				$organizationId = $request->organization_id ?: null;
			} elseif ($user->organization_id) {
				$organizationId = $user->organization_id;
			} elseif (!empty($user->organizations_ids)) {
				$organizationId = $user->organizations_ids[0];
			}

			// Determine is_public (only users with permission can set it)
			$isPublic = false;
			if ($user->ableTo('trainings.set_public')) {
				$isPublic = $request->has('is_public') ? true : false;
			}

			// On update: non-privileged users cannot change organization_id
			if ($request->id) {
				$existing = Training::find($request->id);
				if ($existing && !$user->ableTo('trainings.set_public')) {
					$organizationId = $existing->organization_id;
					$isPublic = $existing->is_public;
				}
			}

			$img_url = null;
			if ($request->hasFile('image'))
			{
				$img_url = $request->file('image');
				$img_url = $img_url->store('training', ['disk' => 'public_uploads']);
				Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 350, function ($con) {
					$con->aspectRatio();
				})->save();
			}

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
					'user_id' => $user->id,
					'image' => $img_url,
					'organization_id' => $organizationId,
					'is_public' => $isPublic,
				]
			);

			$expertises = [];
			if( !empty($request->CoursCategories) )
			{
				foreach( $request->CoursCategories as $expertiseId )
				{
					$expertises[] = [
						'categories_id' => $expertiseId,
						'training_id' => $training->id
					];
				}
			}

			DB::table('categories_training')->where('training_id', $training->id)->delete();
			// إدراج الخبرات دفعة واحدة
			DB::table('categories_training')->insert($expertises);

			if( !empty( array_filter( $request->Tags ) ) )
			{
				$tags = [];
				foreach( $request->Tags as $tagName )
				{
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
				DB::table('tag_training')->where('training_id', $training->id)->delete();
				// إدراج الـ tags المرتبطة بالتدريب دفعة واحدة في الجدول الوسيط
				DB::table('tag_training')->insert($tags);
			}

			return redirect('/dashboard/training/all/')->with(['class' => 'success', 'message' => 'Saved']);
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@store: ' . $e->getMessage());
		}
	}

	public function delete($id, Request $request)
	{
		try {
			if ($request) {
				$training = Training::visibleToUser()->where('id', $id)->first();
				if (!$training) {
					return response()->json(['state' => 'forbidden'], 403);
				}
				$training->delete();
				TrainingContent::where('training_id', $id)->delete();
				DB::table('tag_training')->where('training_id', $id)->delete();
				DB::table('categories_training')->where('training_id', $id)->delete();
				return response()->json(['state' => 'success']);
			} else {
				return response()->json(['state' => 'failed'], 500);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@delete: ' . $e->getMessage());
		}
	}

	public function addcontent($id)
	{
		try {
			$check = Training::findOrFail($id);
			$arr = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get(['title', 'arr']);
			if (count($arr) == 0) {
				$arr = [];
			}
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
			if ($request->tcontent) {
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
		try {
			if ($request) {
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

	public function timeTracking(Request $request)
	{
		
		Log::info($request->all());
		$user= Auth::user();
		if($user->hasRole('Registered')){
			
			$request->validate([
				'duration_minutes' => 'required|integer|min:1',
				'trainingId' => 'required|integer',
			]);

			$training = TrainingSession::where('training_Address', $request->trainingId)->where('step_id', $user?->company?->step)->first();
			if( !$training ) { return response()->json(['status' => 'failed']); }

			TrainingDuration::create([
				'user_id' => $user->id,
				'company_id' => $user->company_id,
				'cycle_id' => $user->company->cycle,
				'step_id' => $user->company->step,
				'training_sessions_id' => $training->id,
				'page_url' => $request->page_url,
				'duration_minutes' => $request->duration_minutes,
			]);

			$totalDuration = TrainingDuration::where('training_sessions_id', $training->id)->sum('duration_minutes');

			TrainingSession::where('id', $training->id)->update([
				'duration' => $totalDuration,
			]);

			return response()->json(['status' => 'success']);
		}
	}

	public function results($id = 0)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$sessions = TrainingSession::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('step', function ($query) use ($orgIds) {
					$query->whereHas('cycle', function ($qer) use ($orgIds) {
						$qer->whereIn('organization_id', $orgIds);
					});
				});
			})->where('type', 'online')->orderBy('datetime')->get();
			if ($id == 0) { $id = $sessions->pluck('id')->first(); }
			$csession = TrainingSession::where('type', 'online')->where('id', $id)->first();
			$contents = 0;

			if ($csession) {
				$contents = TrainingContent::where('training_id', $csession->training_Address)->count();
			}

			$get_online_session = Step::where('id', $csession?->step_id)->orderBy('arr')->get();
			$companies = collect([]);
			$companies_cycle_ids = $get_online_session->pluck('cycle_id')->toArray();
			if( count( $companies_cycle_ids ) > 0 )
			{
				$companies = Company::whereIn('cycle', $companies_cycle_ids)->get();
			}
			
			$uniqueCycleIds = $companies->pluck('cycle')->unique();
			$steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();

			return view('admin.settings.training.results', compact('csession', 'sessions', 'id', 'contents', 'steps'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@myQuiz: ' . $e->getMessage());
		}
	}
	public function sessions(Request $request, $id = 0)
	{
		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			$original_step_id = $id;

			$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 4)->with(['cycle', 'cycle.organization'])->get();
			$cycles = $steps->pluck('cycle')->unique('id')->values();

			if ($id == 0) {
				$id = $steps->pluck('id')->first();
			}
			$steps_ids = $steps->pluck('id')->toArray();
			if($request->company_id != 0) { 
				$company = Company::find($request->company_id);
				$id = $company->step;
			}
			
			// $sessions = TrainingSession::where('step_id', $id)->with('reports')->orderBy('datetime', 'ASC');
			$current_step = $steps->where('id', $id)->first();
			if( !$current_step ) { return abort(404); }
			$sessions = TrainingSession::with('reports')->orderBy('datetime', 'ASC');
			if( $request->company_id != 0 )
			{ 
				$company = Company::find($request->company_id);
				$sessions = $sessions->where('step_id', $company->step);
			}
			else
			{
				$sessions = $sessions->whereIn('step_id', $steps_ids);
			}
			if( $original_step_id != 0 )
			{
				$sessions = $sessions->where('step_id', $original_step_id);
			}
			$sessions = $sessions->get();

			$online = Training::visibleToUser()->get();
			// if ($request->type != 0) {
			// 	$sessions = $sessions->where('type', $request->type);
			// }
			// if ($request->dateFrom != 0) {
			// 	$sessions = $sessions->where('datetime', '>=', $request->dateFrom);
			// }

			// if ($request->dateTo != 0) {
			// 	$sessions = $sessions->where('datetime', '<=', $request->dateTo);
			// }

			// dd($sessions->toArray());
			
			$program_steps = $steps->where('cycle_id', $current_step->cycle->id)->values();
			return view('admin/settings/training/sessions', compact('steps', 'program_steps', 'id', 'sessions', 'cycles', 'online', 'current_step'));
		} catch (\Throwable $e) {
			if( $e->getStatusCode() == 404 ) { return abort(404); }
			Log::error('Error in TrainingController@sessions: ' . $e->getMessage());
		}
	}

	public function mysessions()
	{
		try {
			$sessions = collect([]);
			$company = Auth::user()->company;
			if( $company )
			{
				$steps = $company?->cycledata?->steps;
				if( !empty( $steps ) )
				{
					$now = time();
					$steps_ids = $steps->filter(function ($step) use ($now) {
						return $step->from->timestamp <= $now && $step->to->timestamp >= $now;
					})->values()->pluck('id')->toArray();
					$sessions = TrainingSession::where('id', '>', 0);
					// where('step_id', Auth::user()->company->step)
					$sessions = $sessions->whereIn('step_id', $steps_ids);
					$sessions = $sessions->orderBy('datetime', 'DESC')->get();
				}
				
			}
			$user = Auth::user();
			$sessions = $sessions->map(function ($session) use ($user) {
				// GV-8301: decide joinability from the raw stored values, before
				// the display formatting below overwrites `enddatetime` (and
				// truncates it to Y-m-d for `online` courses).
				$session->can_join = MeetingWindowService::isOpen(
					TrainingSession::wallClock($session->getRawOriginal('datetime')),
					TrainingSession::wallClock($session->getRawOriginal('enddatetime')),
					$user,
					'founder'
				);

				if ($session->type == "online") {
					$session->datetime1 = FormatDate($session->datetime, 'Y-m-d');
					$session->enddatetime = FormatDate($session->enddatetime, 'Y-m-d');
				} else {
					$session->datetime1 = FormatDate($session->datetime, 'Y-m-d h:i A');
					$session->enddatetime = FormatDate($session->enddatetime, 'Y-m-d h:i A');
				}

				return $session;
			});
			$sessions = $sessions->sortBy('datetime1')->values();

			return view('admin/settings/training/mysessions', compact('sessions'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@mysessions: ' . $e->getMessage());
		}
	}

	public function addsession(Request $request)
	{
		try {
			$addOrEdit = 'add';
			$this->meetingService =  OnlineMeetingFactory::create();

			$dateRange = $request->date_range;
			$type = $request->type;
			$l = app()->getLocale();
			if ($type == 'sessions' || $type == 'offline') {
				$startDate = $request->datetime;
				$startTime = $request->starttime;   // وقت البداية
				$endTime = $request->endtime;   // وقت النهاية
				$start_time_tailing = str_contains($startTime, 'AM') || str_contains($startTime, 'PM') ? '' : ':00';
				$start_datetime = strtotime($startDate . ' ' . $startTime . $start_time_tailing);
				
				$end_time_tailing = str_contains($endTime, 'AM') || str_contains($endTime, 'PM') ? '' : ':00';
				$end_datetime = strtotime($startDate . ' ' . $endTime . $end_time_tailing);
			} else if ($type == 'online') {
				if (empty($dateRange) || !str_contains($dateRange, ' - ')) {
					return back()->with(['message' => 'Please fill all fields', 'class' => 'danger']);
				}
				[$startDate, $endDate] = explode(' - ', $dateRange);
				$start_datetime = strtotime($startDate);
				$end_datetime = strtotime($endDate);
			}

			if ($start_datetime == null || $end_datetime == null) {
				return back()->with(['message' => 'Please fill all fields', 'class' => 'danger']);
			}

			$step = $request->step_id;

			// A cleared/never-selected Step or Program must fail with a clear
			// message here -- not as a raw "step_id cannot be null" SQL error
			// once addorcreat*Session() tries to insert the training row.
			if (empty($step) || empty($request->cycle_id)) {
				return back()->with(['message' => __('cycles.Step_Required'), 'class' => 'danger']);
			}

			$title = $request->title;
			$dsc = $request->dsc;
			$datetime = $start_datetime;
			$original_datetime = date( 'Y-m-d h:i A', $start_datetime );
			$original_end_datetime = date( 'Y-m-d h:i A', $end_datetime );
			$datetimeStamp = Carbon::parse($datetime)->timestamp;
			$end_datetimeStamp = Carbon::parse($end_datetime)->timestamp;
			$start = Carbon::parse($datetime);
			$end = Carbon::parse($end_datetime);
			$duration = $start->diffInMinutes($end);

			if ($type == 'sessions') {
				if (\App\Settings::hasReachedMaxConcurrentSessions($datetimeStamp, $duration)) {
					return back()->with(['message' => __('cycles.MaxConcurrentSessionsReached'), 'class' => 'danger']);
				}
				$_session = $this->addorcreatSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->trainer_name, $request->trainer_email);

				if( $_session['success'] == false )
				{
					return back()->with([
						'message' => $_session['message'],
						'class' => 'danger'
					]);
				}
				$trainingSession = $_session['message'];
				// ======> notify Trainer <=================
				SendTrainingNotificationJob::dispatch($step, $trainingSession->id, $title, $original_datetime, $trainingSession->join_url, null, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email, $addOrEdit, $original_end_datetime, $trainingSession->start_url);
				// ==========> End Notify <================
				return back()->with(['message' => __('cycles.Online_Session_Added_Successfully'), 'class' => 'success']);
			} elseif ($type == 'online') {
				$addorcreatOnlineSession = $this->addorcreatOnlineSession($type, $datetime, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->online);
				if (!in_array(env('APP_ENV', 'local'), ['local'])) {
					SendTrainingNotificationJob::dispatch($step, $addorcreatOnlineSession, $title, $original_datetime, null, null, $type, app()->getLocale(), null, null, $addOrEdit);
				}
				return back()->with(['message' => __('cycles.Online_Training_Added_Successfully'), 'class' => 'success']);
			} elseif ($type == 'offline') {
				$trainingSession = $this->addorcreatOfflineSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $request->training_Address, $request->training_location, $request->trainer_name, $request->trainer_email);
				// ======> notify Trainer <=================
				$join_url = $request->training_location ?? 'No Location';
				$address  = $request->training_Address ?? 'No Address';
				SendTrainingNotificationJob::dispatch($step, $trainingSession, $title, $original_datetime, $join_url, $address, $type, app()->getLocale(), $request->trainer_name, $request->trainer_email, $addOrEdit, $original_end_datetime);
				// ==========> End Notify <================
				return back()->with(['message' => __('cycles.Offline_Session_Added_Successfully'), 'class' => 'success']);
			} else {
				return back()->with(['message' => 'Can\'t add session', 'class' => 'danger']);
			}
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@addsession: ' . $e->getMessage());
			$message = explode('response:', $e->getMessage());
			if(isset($message[1]))
			{
				$error = trim($message[1], "\n");
				$error = json_decode($error);
				$final_output = $error->error->message;
				return back()->with(['message' => $final_output, 'class' => 'danger']);
			}
			return back()->with(['message' => $e->getMessage(), 'class' => 'danger']);
		}
	}
	public function editsession(Request $request, $id)
	{
			try {
					$this->meetingService = OnlineMeetingFactory::create();
					$addOrEdit = 'edit';
					$type = $request->type;
					$start_datetime = null;
					$end_datetime = null;
					$l = app()->getLocale();
	
					if ($type == 'sessions' || $type == 'offline') {
							$startDate = $request->datetime;
							$startTime = date('H:i', strtotime($request->starttime));
							$endTime   = date('H:i', strtotime($request->endtime));
							$start_datetime = $startDate . ' ' . $startTime;
							$end_datetime   = $startDate . ' ' . $endTime;
					} elseif ($type == 'online') {
							if (empty($request->date_range)) {
									return back()->with(['message' => 'Date range is required for online sessions', 'class' => 'danger']);
							}
							[$startDate, $endDate] = explode(' - ', $request->date_range);
							try {
									$start_datetime = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
									$end_datetime   = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
							} catch (\Exception $e) {
									return back()->with(['message' => 'Invalid date format in date range', 'class' => 'danger']);
							}
					} else {
							return back()->with(['message' => "Can't edit session — unknown type", 'class' => 'danger']);
					}
	
					if (is_null($start_datetime) || is_null($end_datetime)) {
							return back()->with(['message' => 'Please fill all required date and time fields', 'class' => 'danger']);
					}
	
					$step     = $request->step_id;
					$title    = $request->title;
					$dsc      = $request->dsc;
					$datetime = $start_datetime;
	
					$datetimeStamp     = Carbon::parse($datetime)->timestamp;
					$end_datetimeStamp = Carbon::parse($end_datetime)->timestamp;
					$start             = Carbon::parse($datetime);
					$end               = Carbon::parse($end_datetime);
					$duration          = $start->diffInMinutes($end);
	
					if ($type == 'sessions') {
							$_session = $this->addorcreatSession(
									$type, $datetime, $duration, $title, $dsc,
									$datetimeStamp, $end_datetimeStamp, $step, $addOrEdit,
									$request->trainer_name, $request->trainer_email, $request->sid
							);
	
							if ($_session['success'] == false) {
									return back()->with(['message' => $_session['message'], 'class' => 'danger']);
							}
	
							$trainingSession = $_session['message'];
							SendTrainingNotificationJob::dispatch(
									$step, $trainingSession->id, $title, $datetime,
									$trainingSession->join_url, null, $type,
									app()->getLocale(), $request->trainer_name, $request->trainer_email, $addOrEdit,
									$end_datetime, $trainingSession->start_url
							);
							return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);
	
					} elseif ($type == 'online') {
							$addorcreatOnlineSession = $this->addorcreatOnlineSession(
									$type, $datetime, $title, $dsc,
									$datetimeStamp, $end_datetimeStamp, $step, $addOrEdit,
									$request->online, $request->sid
							);
							SendTrainingNotificationJob::dispatch(
									$step, $addorcreatOnlineSession, $title, $datetime,
									null, null, $type, app()->getLocale(), null, null, $addOrEdit
							);
							return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);
	
					} elseif ($type == 'offline') {
							$trainingSession = $this->addorcreatOfflineSession(
									$type, $datetime, $duration, $title, $dsc,
									$datetimeStamp, $end_datetimeStamp, $step, $addOrEdit,
									$request->training_Address, $request->training_location,
									$request->trainer_name, $request->trainer_email, $request->sid
							);
	
							if (!$trainingSession) {
									return back()->with(['message' => 'Failed to update session. Please try again.', 'class' => 'danger']);
							}
	
							$join_url = $request->training_location ?? 'No Location';
							$address  = $request->training_Address  ?? 'No Address';
							SendTrainingNotificationJob::dispatch(
									$step, $trainingSession, $title, $datetime,
									$join_url, $address, $type,
									app()->getLocale(), $request->trainer_name, $request->trainer_email, $addOrEdit
							);
							return back()->with(['message' => __('admin.sessionUpdated'), 'class' => 'success']);
					}
	
			} catch (\Throwable $e) {
					Log::error('Error in TrainingController@editsession: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
					return back()->with(['message' => 'An error occurred: ' . $e->getMessage(), 'class' => 'danger']);
			}
	}

	private function addorcreatSession($type, $datetime, $duration, $title, $dsc, $datetimeStamp, $end_datetimeStamp, $step, $addOrEdit, $trainer_name, $trainer_email, $sid = null)
	{
		$this->meetingService =  OnlineMeetingFactory::create();
		if ($type == 'sessions') {
			// Add Zoom
			$topic = "Training (" . $title . ')';
			$data = ['topic' => $topic, "start_time" => $datetime, 'duration' => $duration, 'host_video' => 1, 'participant_video' => 1];
			$onlineMeeting = $this->meetingService->createMeeting($data);
			if (!$onlineMeeting['success']) {
				return [
					'success' => false,
					'message' => $onlineMeeting['data']
				];
				// return "Error";
			}
			$zoomid =  $onlineMeeting['data']['id'];
			$start = $onlineMeeting['data']['start_url'];
			$join = $onlineMeeting['data']['join_url'];
			$passcode = $onlineMeeting['data']['passcode'] ?? null;

			$data = [
				'type' => $type,
				'step_id' => $step,
				'title' => $title,
				'dsc' => $dsc,
				'zoom_id' => $zoomid,
				'zoom_passcode' => $passcode,
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
			return [
				'success' => true,
				'message' => $trainingSession
			];
			// return $trainingSession;
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

	private function addorcreatOfflineSession(
			$type, $datetime, $duration, $title, $dsc,
			$datetimeStamp, $end_datetimeStamp, $step, $addOrEdit,
			$training_Address, $training_location, $trainer_name, $trainer_email, $sid = null
	) {
			$data = [
					'type'              => $type,
					'step_id'           => $step,
					'title'             => $title,
					'dsc'               => $dsc,
					'datetime'          => $datetimeStamp,
					'user_id'           => Auth::id(),
					'training_Address'  => $training_Address,
					'training_location' => $training_location,
					'enddatetime'       => $end_datetimeStamp,
					'trainer_name'      => $trainer_name,
					'trainer_email'     => $trainer_email,
			];
	
			if ($addOrEdit == 'add') {
					$trainingSession = TrainingSession::create($data);
			} else {
					TrainingSession::where('id', $sid)->update($data);
					$trainingSession = TrainingSession::find($sid);
			}
	
			if (!$trainingSession) {
					throw new \RuntimeException("TrainingSession not found for sid={$sid}");
			}
	
			// Generate QR code only on create, or regenerate if explicitly needed
			try {
					$attendanceUrl = url("/dashboard/training/session-attendance-offline/{$trainingSession->id}");
					$qrFileName    = 'training_' . $trainingSession->id . '.png';
					$qrFilePath    = '/qrcodes/' . $qrFileName;
					$qrCode        = QrCode::format('png')->size(300)->generate($attendanceUrl);
	
					Storage::disk('QrCodePublic')->put($qrFileName, $qrCode);
					$trainingSession->update(['QrCode' => $qrFilePath]);
			} catch (\Throwable $e) {
					Log::error('QR Code generation failed for session ' . $trainingSession->id . ': ' . $e->getMessage());
					// QR failure is non-fatal — session was already saved successfully
			}
	
			return $trainingSession;
	}

	public function session(Request $request)
	{
		if ($request->id) {
			$sessionData = TrainingSession::findOrFail($request->id);
			$session = [];
			$session['id'] = $sessionData->id;
			$session['title'] = $sessionData->title;
			$session['dsc'] = $sessionData->dsc;
			$session['type'] = $sessionData->type;
			if ($sessionData->type == 'online') {
				$session['daterange'] = FormatDate($sessionData->datetime, 'Y-m-d') . ' - ' . FormatDate($sessionData->enddatetime, 'Y-m-d');
			} else {
				$session['daterange'] = FormatDate($sessionData->datetime, 'Y-m-d');
				$session['fromtime'] = FormatDate($sessionData->datetime, 'h:i A');
				$session['endtime'] = FormatDate($sessionData->enddatetime, 'h:i A');
			}
			if ($sessionData->type == 'sessions') {
				$session['training_Address'] = $sessionData->training_Address;
			} elseif ($sessionData->type == 'offline') {
				$session['training_Address'] = $sessionData->training_Address;
				$session['training_location'] = $sessionData->training_location;
			} elseif ($sessionData->type == 'online') {
				$session['course_id'] = $sessionData->training_Address;
			}
			$session['trainer_name'] = $sessionData->trainer_name;
			$session['trainer_email'] = $sessionData->trainer_email;
			$session['QrCode'] = $sessionData->QrCode;
			$session['duration'] = $sessionData->duration;
			$session['start_url'] = $sessionData->start_url;
			$session['join_url'] = $sessionData->join_url;
			// GV-8301: the details modal hides its Join button once the window closes.
			$session['can_join'] = MeetingWindowService::isOpen(
				TrainingSession::wallClock($sessionData->getRawOriginal('datetime')),
				TrainingSession::wallClock($sessionData->getRawOriginal('enddatetime')),
				Auth::user(),
				'founder'
			);
			$session['created_at'] = $sessionData->created_at;
			$session['step_id'] = $sessionData->step_id;
			$session['cycle_id'] = $sessionData->step?->cycle_id;

			return response()->json($session);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function getattendanceOffline(Request $request)
	{
		if ($request->id) {
			$attendanceData = AttendanceTrainingOffline::where('training_session_id', $request->id)->with('user', 'trainingSession', 'step', 'cycle', 'company')->get();
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
		try {
			if ($request->id) {
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
				if (!file_exists(public_path('qrcodes'))) {
					mkdir(public_path('qrcodes'), 0775, true);
				}
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
			return response()->json(['state' => 'failed', 'message' => $e->getMessage()], 500);
		}
	}

	public function delsession($id, Request $request)
	{
		try {
			// $zoomid = TrainingSession::findOrFail($id)->zoom_id;
			if ($request) {
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
		try {
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

			$serviceClass = get_class($this->meetingService);
        	$isZoom = stripos($serviceClass, 'zoom') !== false;
        	$isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;
			if ($isZoom) {
             if (!empty($response['success'])) {
                $participants = $response['data']['participants'] ?? [];

                foreach ($participants as $participant) {
                    $joinDate = isset($participant['join_time']) ? Carbon::parse($participant['join_time'])->utc()->toDateTimeString() : null;
                    $endDate = isset($participant['leave_time']) ? Carbon::parse($participant['leave_time'])->utc()->toDateTimeString() : null;

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
                Log::warning('Zoom sessionReport: no success', ['response' => $response]);
                return redirect()->back()->with(['message2' => 'session report is not available yet']);
            }
        } elseif ($isTeams) {
			$participants = $response['participants'] ?? [];

            if (empty($participants)) {
                Log::warning('Teams sessionReport: participants empty', ['response' => $response]);
                return redirect()->back()->with(['message2' => 'session report is not available yet', 'debug' => $response]);
            }

            foreach ($participants as $p) {
                $identity = $p['identity'] ?? null;

                $participantUserId = null;
                $participantName = null;

                if (is_array($identity)) {
                    if (isset($identity['user']) && is_array($identity['user'])) {
                        $participantUserId = $identity['user']['id'] ?? null;
                        $participantName = $identity['user']['displayName'] ?? $identity['user']['displayname'] ?? null;
                    } else {
                        $participantUserId = $identity['id'] ?? null;
                        $participantName = $identity['displayName'] ?? $identity['displayname'] ?? null;
                    }
                }

                $email = $p['emailAddress'] ?? null;
                $duration = $p['totalAttendanceInSeconds'] ?? 0;

                $interval = $p['attendanceIntervals'][0] ?? null;
                $joinDate = $interval['joinDateTime'] ?? $interval['joinDate'] ?? null;
                $leaveDate = $interval['leaveDateTime'] ?? $interval['leaveDate'] ?? null;

                try {
                    $joinDateParsed = $joinDate ? Carbon::parse($joinDate)->utc()->toDateTimeString() : null;
                } catch (\Throwable $ex) {
                    $joinDateParsed = null;
                }
                try {
                    $leaveDateParsed = $leaveDate ? Carbon::parse($leaveDate)->utc()->toDateTimeString() : null;
                } catch (\Throwable $ex) {
                    $leaveDateParsed = null;
                }

                SessionReport::updateOrCreate(
                    [
                        'session_id' => $session ? $session->id : null,
                        'participant_user_id' => $participantUserId,
                    ],
                    [
                        'mentorship_request_id' => $mentorshipRequest ? $mentorshipRequest->id : null,
                        'participant_email' => $email,
                        'participant_name' => $participantName,
                        'participant_join_date' => $joinDateParsed,
                        'participant_end_date' => $leaveDateParsed,
                        'attendance_duration' => $duration ?? 0,
                    ]
                );
            }
            
         return redirect()->back()->with(['message' => 'Session Report Updated', 'class' => 'success']);
        } 
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessionReport: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return redirect()->back()->with(['message2' => 'session_report_not_available']);
		}
	}

	public function sessionRecord($id)
	{
		$this->meetingService =  OnlineMeetingFactory::create();
		try {
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
            return redirect()->back()->with([
                'message2' => 'Session Recording is not available yet'
            ]);
        }

        $response = $this->meetingService->getSessionRecord($zoomId);

        if (!empty($response['data'][0])) {
            $file = $response['data'][0];

            $recordingId = $file['id'] ?? null;
            $recordingUrl = $file['recordingContentUrl'] ?? $file['meetingId'] ?? null;
            $createdDateTime = isset($file['createdDateTime']) ? Carbon::parse($file['createdDateTime'])->utc()->toDateTimeString() : null;

            if ($recordingId) {
                SessionRecording::updateOrCreate(
                    ['recording_id' => $recordingId],
                    [
                        'session_id' => $sessionId,
                        'mentorship_request_id' => $mentorshipRequest?->id,
                        'recording_type' => 'Teams Recording',
                        'recording_view_url' => $recordingUrl,
                        'recording_download_url' => $recordingUrl,
                        'recording_time' => $createdDateTime,
                        'passcode' => null,
                    ]
                );

                return redirect()->back()->with(['message' => 'Session Record updated', 'class' => 'success']);
            }
        }

        if (!empty($response['data']['recording_files'])) {
            $file = $response['data']['recording_files'][0] ?? null;

            if ($file) {
                $recordingTime = isset($file['recording_start']) ? Carbon::parse($file['recording_start'])->utc()->toDateTimeString() : null;
                $password = $response['data']['password'] ?? null;

                SessionRecording::updateOrCreate(
                    ['recording_id' => $file['id']],
                    [
                        'session_id' => $sessionId,
                        'mentorship_request_id' => $mentorshipRequest?->id,
                        'recording_type' => $file['recording_type'] ?? null,
                        'recording_view_url' => $file['play_url'] ?? null,
                        'recording_download_url' => $file['download_url'] ?? null,
                        'recording_time' => $recordingTime,
                        'passcode' => $password,
                    ]
                );

                return redirect()->back()->with(['message' => 'Session Record updated', 'class' => 'success']);
            }
        }

        return redirect()->back()->with(['message2' => 'Session Recording is not available yet']);

		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessionRecord: ' . $e->getMessage(), [
				'trace' => $e->getTraceAsString()
			]);

			return redirect()->back()->with(['message2' => 'Session Recording is not available yet']);
		}
	}


	public function getdsc($id)
	{
		if ($id) {
			// $session = TrainingSession::findOrFail($id);
			$session = "SELECT ts.*, t.title FROM training_sessions as ts";
			$session .= " LEFT JOIN trainings as t ON ts.training_Address = t.id ";
			$session .= "WHERE ts.id = ?";
			$result = DB::select($session, [$id]);
			$session = collect($result)->first();


			if (strtolower(trim($session->type)) == "online") {
				$session->datetime1 = FormatDate($session->datetime, 'Y-m-d');
				$session->enddatetime = FormatDate($session->enddatetime, 'Y-m-d');
				$session->Course_id = $session->training_Address;
				$session->course_Name = $session->title;
			} else {
				$session->datetime1 = FormatDate($session->datetime, 'Y-m-d h:i A');
				$session->enddatetime = FormatDate($session->enddatetime, 'Y-m-d h:i A');
				$session->starttime = FormatDate($session->datetime, 'h:i A');
				$session->endtime = FormatDate($session->enddatetime, 'h:i A');
				$session->date = FormatDate($session->datetime, 'Y-m-d');
			}


			return response()->json($session);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function create($id = 0)
	{
		try {
			$courses = Training::visibleToUser()->orderBy('created_at', 'DESC')->get();
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

			$user = Auth::user();
			$organizations = $user->ableTo('trainings.set_public') ? \App\Organization::all() : collect();

			return view('admin/settings/training/newCours', compact('randomNumber', 'Tags', 'expertises', 'client', 'Quizzes', 'organizations'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@create: ' . $e->getMessage());
		}
	}

	public function editTR($id)
	{
		try {
			$course = Training::visibleToUser()->where('id', $id)->orderBy('created_at', 'DESC')->first();
			if (!$course) {
				abort(403);
			}
			$contents = TrainingContent::where('training_id', $id)->orderBy('arr', 'ASC')->get();

			$expertises = Expertise::where('parent_id', null)->get();
			$Quizzes = Quizzes::all();

			$instructors = User::whereHas('roles', function ($query) {
				$query->where('name', 'Trainer');
			})->get();
			$training = Training::find($id);

			$tags = $training->tags;

			$user = auth()->user();
			$organizations = $user->ableTo('trainings.set_public') ? \App\Organization::all() : collect();

			return view('admin/settings/training/editCours', compact('course', 'id', 'contents', 'expertises', 'Quizzes', 'instructors', 'tags', 'organizations'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@editTR: ' . $e->getMessage());
		}
	}

	public function showCourse($id)
	{
		try {
			$course = Training::visibleToUser()->where('id', $id)->orderBy('created_at', 'DESC')->first();
			if (!$course) {
				abort(403);
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

	public function attendanceOffline(Request $request, $id)
	{
		try {
			$session = TrainingSession::find($id);
			$step_Traning = $session->step_id;
			$cycle_Traning = $session->step->cycle->id;
			$user = auth()->user();
			$userId = $user->id;
			$companyId = $user->company_id;
			$cycle_user = $user->company->cycle;
			$step_user = $user->company->step;

			if ($step_user == $step_Traning) {
				if ($session->datetime > now()) {
					return response()->json(['message' => 'WaitForStartAttendece', 'Start' => $session->datetime]);
				}
				if ($session->enddatetime < now()) {
					return response()->json(['message' => 'SessionEnded', 'Ended' => $session->enddatetime]);
				}
				$insert = DB::table('attendance_training_offline')
					->where('training_session_id', $session->id)
					->where('user_id', $userId)->first();
				if (!empty($insert)) {
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
				} else {
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

	public function sessionsReport(Request $request)
	{

		try {
			$user = auth()->user();
			$orgIds = $user->organizations_ids;

			$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 4)->get();
			$sessions = TrainingSession::with('reports')->orderBy('datetime', 'ASC');
			$online = Training::visibleToUser()->get();
			if ($request->type != 0) {
				$sessions = $sessions->where('type', $request->type);
			}
			
			if ($request->cycle != 0) {
				$cycleId = $request->cycle;
				// $sessions = $sessions->where('type', $request->type);
				$sessions = $sessions->whereHas('step', function ($q) use ($cycleId) {
					$q->where('cycle_id', $cycleId);
				});
			
			}
			
			
			if ($request->dateFrom != 0) {
				$sessions = $sessions->where('datetime', '>=', $request->dateFrom);
			}

			if ($request->dateTo != 0) {
				$sessions = $sessions->where('datetime', '<=', $request->dateTo);
			}

			$sessions = $sessions->get();
			
			return view('admin/settings/training/sessionsReport', compact('sessions'));
		} catch (\Throwable $e) {
			Log::error('Error in TrainingController@sessions: ' . $e->getMessage());
		}
	}

	/**
	 * Fetch session reports and recordings data for modal
	 */
	public function getSessionReportsData(Request $request)
	{
		try {
			$sessionId = $request->id;
			
			if (!$sessionId) {
				return response()->json([
					'success' => false,
					'message' => __('admin.session_id_required')
				], 400);
			}

			$session = TrainingSession::with(['reports', 'recordings', 'step.cycle'])
				->find($sessionId);

			if (!$session) {
				return response()->json([
					'success' => false,
					'message' => __('admin.session_not_found')
				], 404);
			}

			// Store original counts for debugging
			$originalReportsCount = $session->reports->count();
			$originalRecordingsCount = $session->recordings->count();

			// Auto-fetch reports if none exist and session is a Zoom/Teams session
			if ($session->reports->count() === 0 && $session->type === 'sessions' && $session->zoom_id) {
				try {
					// Log::info('Auto-fetch reports: Starting for session ' . $sessionId . ' with zoom_id ' . $session->zoom_id);
					$this->meetingService = OnlineMeetingFactory::create();
					if ($this->meetingService && method_exists($this->meetingService, 'getSessionReport')) {
						// Log::info('Auto-fetch reports: Calling getSessionReport for zoom_id ' . $session->zoom_id);
						$response = $this->meetingService->getSessionReport($session->zoom_id);
						// Log::info('Auto-fetch reports: API Response', ['response' => $response]);
						
						$serviceClass = get_class($this->meetingService);
						$isZoom = stripos($serviceClass, 'zoom') !== false;
						$isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;
						
						// Process Zoom reports
						if ($isZoom && !empty($response['success']) && !empty($response['data']['participants'])) {
							// Log::info('Auto-fetch reports: Processing Zoom participants', ['count' => count($response['data']['participants'])]);
							foreach ($response['data']['participants'] as $participant) {
								$joinDate = isset($participant['join_time']) ? Carbon::parse($participant['join_time'])->utc()->toDateTimeString() : null;
								$endDate = isset($participant['leave_time']) ? Carbon::parse($participant['leave_time'])->utc()->toDateTimeString() : null;

								SessionReport::updateOrCreate(
									[
										'session_id' => $session->id,
										'participant_user_id' => $participant['user_id'] ?? null,
									],
									[
										'participant_email' => $participant['user_email'] ?? null,
										'participant_name' => $participant['name'] ?? null,
										'participant_join_date' => $joinDate,
										'participant_end_date' => $endDate,
										'attendance_duration' => $participant['duration'] ?? 0,
									]
								);
							}
							// Refresh the session data to include new reports
							$session->load('reports');
						}
						// Process Teams reports
						elseif ($isTeams && !empty($response['participants'])) {
							foreach ($response['participants'] as $p) {
								$participantName = $p['displayName'] ?? $p['identity']['user']['displayName'] ?? null;
								$participantEmail = $p['identity']['user']['email'] ?? null;
								$participantUserId = $p['identity']['user']['id'] ?? null;
								$duration = isset($p['totalAttendanceInSeconds']) ? $p['totalAttendanceInSeconds'] / 60 : 0;

								$interval = $p['attendanceIntervals'][0] ?? null;
								$joinDate = $interval['joinDateTime'] ?? $interval['joinDate'] ?? null;
								$leaveDate = $interval['leaveDateTime'] ?? $interval['leaveDate'] ?? null;

								try {
									$joinDateParsed = $joinDate ? Carbon::parse($joinDate)->utc()->toDateTimeString() : null;
								} catch (\Throwable $ex) {
									$joinDateParsed = null;
								}
								try {
									$leaveDateParsed = $leaveDate ? Carbon::parse($leaveDate)->utc()->toDateTimeString() : null;
								} catch (\Throwable $ex) {
									$leaveDateParsed = null;
								}

								SessionReport::updateOrCreate(
									[
										'session_id' => $session->id,
										'participant_user_id' => $participantUserId,
									],
									[
										'participant_email' => $participantEmail,
										'participant_name' => $participantName,
										'participant_join_date' => $joinDateParsed,
										'participant_end_date' => $leaveDateParsed,
										'attendance_duration' => $duration ?? 0,
									]
								);
							}
							// Refresh the session data to include new reports
							$session->load('reports');
							// Log::info('Completed processing reports for session ' . $sessionId . ', final count: ' . $session->reports->count());
						}
					}
				} catch (\Throwable $e) {
					// Log::info('Auto-fetch reports failed for session ' . $sessionId . ': ' . $e->getMessage());
				}
			}

			// Auto-fetch recordings if none exist and session is a Zoom/Teams session
			if ($session->recordings->count() === 0 && $session->type === 'sessions' && $session->zoom_id) {
				try {
					// Log::info('Auto-fetching recordings for session ' . $sessionId . ' with zoom_id: ' . $session->zoom_id);
					$this->meetingService = OnlineMeetingFactory::create();
					if ($this->meetingService && method_exists($this->meetingService, 'getSessionRecord')) {
						$response = $this->meetingService->getSessionRecord($session->zoom_id);
						// Log::info('Recordings API response for session ' . $sessionId . ': ' . json_encode($response));
						
						$serviceClass = get_class($this->meetingService);
						$isZoom = stripos($serviceClass, 'zoom') !== false;
						$isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;
						
						// Process Zoom recordings
						if ($isZoom && !empty($response['data']['recording_files'])) {
							foreach ($response['data']['recording_files'] as $file) {
								$recordingTime = isset($file['recording_start']) ? Carbon::parse($file['recording_start'])->utc()->toDateTimeString() : null;
								$password = $response['data']['password'] ?? null;

								SessionRecording::updateOrCreate(
									['recording_id' => $file['id']],
									[
										'session_id' => $session->id,
										'recording_type' => $file['recording_type'] ?? null,
										'recording_view_url' => $file['play_url'] ?? null,
										'recording_download_url' => $file['download_url'] ?? null,
										'recording_time' => $recordingTime,
										'passcode' => $password,
									]
								);
							}
							// Refresh the session data to include new recordings
							$session->load('recordings');
							// Log::info('Processed Zoom recordings for session ' . $sessionId . ', count: ' . $session->recordings->count());
						}
						// Process Teams recordings
						elseif ($isTeams && !empty($response['data'][0])) {
							$file = $response['data'][0];
							$recordingId = $file['id'] ?? null;
							$recordingUrl = $file['recordingContentUrl'] ?? $file['meetingId'] ?? null;
							$createdDateTime = isset($file['createdDateTime']) ? Carbon::parse($file['createdDateTime'])->utc()->toDateTimeString() : null;

							if ($recordingId) {
								SessionRecording::updateOrCreate(
									['recording_id' => $recordingId],
									[
										'session_id' => $session->id,
										'recording_type' => 'Teams Recording',
										'recording_view_url' => $recordingUrl,
										'recording_download_url' => $recordingUrl,
										'recording_time' => $createdDateTime,
										'passcode' => null,
									]
								);
								// Refresh the session data to include new recordings
								$session->load('recordings');
								// Log::info('Processed Teams recordings for session ' . $sessionId . ', count: ' . $session->recordings->count());
							}
						} else {
							// Log::info('No recordings data found in response for session ' . $sessionId);
						}
					}
				} catch (\Throwable $e) {
					// Log::info('Auto-fetch recordings failed for session ' . $sessionId . ': ' . $e->getMessage());
				}
			}

			// Prepare recordings data
			$recordings = $session->recordings->map(function ($recording) {
				// Format recording time properly for JavaScript
				$recordingTime = $recording->recording_time;
				if ($recordingTime) {
					try {
						// Treat stored time as UTC and convert to ISO string
						$recordingTime = Carbon::parse($recordingTime, 'UTC')->toISOString();
					} catch (\Exception $e) {
						$recordingTime = null;
					}
				}

				return [
					'recording_type' => $recording->recording_type,
					'recording_time' => $recordingTime,
					'recording_view_url' => $recording->recording_view_url,
					'recording_download_url' => $recording->recording_download_url,
				];
			});

			// Recalculate reports data after potential auto-fetch
			$reports = $session->reports->map(function ($report) {
				// Format join and leave times properly for JavaScript
				$joinDate = $report->participant_join_date;
				if ($joinDate) {
					try {
						$joinDate = Carbon::parse($joinDate, 'UTC')->toISOString();
					} catch (\Exception $e) {
						$joinDate = null;
					}
				}
				
				$endDate = $report->participant_end_date;
				if ($endDate) {
					try {
						$endDate = Carbon::parse($endDate, 'UTC')->toISOString();
					} catch (\Exception $e) {
						$endDate = null;
					}
				}
				
				return [
					'participant_name' => $report->participant_name,
					'participant_email' => $report->participant_email,
					'participant_join_date' => $joinDate,
					'participant_end_date' => $endDate,
					'attendance_duration' => $report->attendance_duration,
				];
			});

			// Calculate summary data after auto-fetch operations
			$totalDuration = $session->reports->sum('attendance_duration');
			$totalParticipants = $session->reports->count();
			$totalRecordings = $session->recordings->count();

			// Get recording passcode (from first recording if available)
			$recordingPasscode = $session->recordings->first()->passcode ?? null;

			return response()->json([
				'success' => true,
				'data' => [
					'session' => [
						'id' => $session->id,
						'title' => $session->title,
						'type' => $session->type,
						'datetime' => $session->datetime,
						'enddatetime' => $session->enddatetime,
					],
					'summary' => [
						'total_duration' => $totalDuration,
						'total_participants' => $totalParticipants,
						'total_recordings' => $totalRecordings,
					],
					'reports' => $reports,
					'recordings' => $recordings,
					'recording_passcode' => $recordingPasscode,
				],
				'debug' => [
					'session_id' => $sessionId,
					'zoom_id' => $session->zoom_id,
					'session_type' => $session->type,
					'reports_count_before' => $originalReportsCount,
					'recordings_count_before' => $originalRecordingsCount,
					'reports_count_after' => $session->reports->count(),
					'recordings_count_after' => $session->recordings->count(),
					'auto_fetch_reports_attempted' => $originalReportsCount === 0 && $session->type === 'sessions' && $session->zoom_id,
					'auto_fetch_recordings_attempted' => $originalRecordingsCount === 0 && $session->type === 'sessions' && $session->zoom_id,
					'meeting_service_available' => class_exists('App\\Services\\OnlineMeetingFactory'),
					'session_conditions' => [
						'has_zoom_id' => !empty($session->zoom_id),
						'is_sessions_type' => $session->type === 'sessions',
						'reports_empty' => $originalReportsCount === 0,
						'recordings_empty' => $originalRecordingsCount === 0
					]
				]
			]);

		} catch (\Throwable $e) {
			// Log::error('Error in TrainingController@getSessionReportsData: ' . $e->getMessage());
			
			return response()->json([
				'success' => false,
				'message' => __('admin.error_fetching_session_data')
			], 500);
		}
	}

	public function join($id)
	{
		$session = TrainingSession::findOrFail($id);
		
		$user = Auth::user();

		// GV-8301: the join route is directly reachable, so the window is
		// enforced here too and not only by hiding the button in the view.
		// Attendance must not be recorded for a session that can no longer be joined.
		$joinOpen = MeetingWindowService::isOpen(
			TrainingSession::wallClock($session->getRawOriginal('datetime')),
			TrainingSession::wallClock($session->getRawOriginal('enddatetime')),
			$user,
			'founder'
		);

		if (!$joinOpen) {
			return redirect()
				->route('trainings.my_sessions_mine')
				->with('error', __('admin.Finished'));
		}
		
		AttendanceTrainingOffline::updateOrCreate(
			[
				'training_session_id' => $session->id,
				'user_id' => $user->id,
				'company_id' => $user->company_id,
			],
			[
				
				'step_id' => $session->step_id,
				'cycle_id' => $session->step->cycle_id,
				'attendance' => true,
				'location' => 'online',
				'created_at' => now(),
			]
		);

		
		return redirect()->away($session->join_url);
	}

}
