<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use Exception;
use App\Company;
use App\Surveys;
use App\Schedule;
use Carbon\Carbon;
use App\SurveyScopes;
use App\CyclesMentors;
use App\Events\WebMessage;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Jobs\ExportMentorsData;
use App\Events\RequestMentorship;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMentorshipRequestJob;
use App\Factories\OnlineMeetingFactory;
use App\Events\MentorshipSessionUpdated;
use App\Http\Resources\MentorResource;
use App\Jobs\SendMentorshipNotification;
use App\Notifications\MentorshipSession;
use App\Notifications\MentorshipRequestNotification;

class MentorsController extends Controller
{
	protected $meetingService;
	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->meetingService = OnlineMeetingFactory::create();
		$this->whatsAppService = $whatsAppService;
	}

	public function exportMentorship(Request $request)
	{
		// تحقق من أن البريد الإلكتروني موجود
		$data4 = [
			'request' => $request->input('request'),
			'userEmail' => auth()->user()->email
		];

		// إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
		ExportMentorsData::dispatch($data4);
		return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
	}
	public function index(Request $request, $id = 0)
	{
		$organization_id = auth()->user()->organization_id;
		if ($id == 0) {
			$id = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->orderBy('start', 'DESC')->pluck('id')->first();
		}
		$companies = Company::where('cycle', $id)->where('step', '>', 0)->get();
		$mentors = CyclesMentors::where('cycle_id', $id)->get();
		$cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->get();

		if ($request->step_id != 0) {
			$companies = Company::where('cycle', $id)
				->where('step', '>', 0)
				->when($request->has('step_id'), function ($query) use ($request) {
					return $query->where('step', $request->step_id);
				})
				->get();
		}


		return view('admin.settings.mentorship.index', compact('mentors', 'id', 'companies', 'cycles'));
	}

	public function list(Request $request, $id = 0)
	{
		$l = app()->getLocale();
		$organization_id = auth()->user()->organization_id;
		if ($id == 0) {
			$id = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->orderBy('start', 'DESC')->pluck('id')->first();
		}
		// $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
		$mentors = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'mentor')
			->select('users.*')
			->get();
		$cList = CyclesMentors::where('cycle_id', $id)->pluck('user_id')->toArray();
		$cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->get();

		$selectedSpecialization = $request->specialization; // الحصول على قيمة التصفية من query string

		if ($selectedSpecialization != 0) {
			$mentors = $mentors->filter(function ($mentor) use ($selectedSpecialization, $l) {
				$mentorSpecialization = $mentor->bio->{'specialization_' . $l} ?? null;

				return !is_null($mentorSpecialization) && $mentorSpecialization == $selectedSpecialization;
			});
		}


		if ($request->rate != 0) {
			$rating = $request->rate;
			$mentors = $mentors->filter(function ($mentor) use ($rating) {
				$averageRating = $mentor->mrating() ?? 0; // حساب متوسط التقييم للمدرب
				return round($averageRating) == $rating; // مقارنة التقييم مع القيمة المختارة
			});
		}

		// تصفية حسب حالة الـ checked
		if ($request->assigning != 0) {
			$checkedStatus = $request->assigning; // القيمة ستكون "checked" أو "unchecked"

			$mentors = $mentors->filter(function ($mentor) use ($checkedStatus, $cList) {
				$isChecked = in_array($mentor->id, $cList);

				// إذا كان المستخدم اختار "checked"، نعيد فقط المدربين الموجودين في الدورة
				if ($checkedStatus == 'checked') {
					return $isChecked;
				}
				// إذا كان المستخدم اختار "unchecked"، نعيد فقط المدربين غير المسجلين
				elseif ($checkedStatus == 'Notchecked') {
					return !$isChecked;
				}
				return true; // في حالة عدم تحديد أي فلتر
			});
		}



		return view('admin.settings.mentorship.assign', compact('mentors', 'id', 'cList', 'cycles'));
	}

	public function assign($id, Request $request)
	{
		if ($request->id) {
			$users = explode(',', $request->selected_mentors);
			CyclesMentors::where('cycle_id', $request->id)->delete();
			if ($users) {
				foreach ($users as $user) {
					CyclesMentors::insert(['cycle_id' => $request->id, 'user_id' => $user]);
				}
			}
		}
		return back()->with(['message' => 'Request successfully added', 'class' => 'success']);
	}

	public function list_surveys($id, Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->with(['steps' => function ($q) {
			$q->orderBy('arr');
		}])->find($id);

		if (!empty($cycle)) {
			$organization_id = $cycle->organization_id;
			$surveys = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'mentorship')->with(['survey', 'responses'])->get();
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

			$available_surveys = Surveys::where('organization_id', $cycle->organization_id)->get();
			return view('admin.settings.mentorship.surveys', compact('surveys', 'cycle', 'available_surveys'));
		} else {
			abort(404);
		}
	}

	public function total_surveys()
	{
		if (Auth::user()->hasAnyRole('organizationManager')) {
			$organization_id = auth()->user()->organization_id;
			$surveys = SurveyScopes::where('organization_id', $organization_id)->with(['survey', 'responses'])->get();
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
		} else {
			$organization_id = auth()->user()->organization_id;
			$surveys = SurveyScopes::with(['survey', 'responses'])->get();
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
		}


		return view('admin.surveys.totalsurvey', compact('surveys'));
	}

	public function create_survey($id, Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->find($id);

		if (!empty($cycle)) {
			$organization_id = $cycle->organization_id;
			$surveys = Surveys::where('organization_id', $organization_id)->get();
			return view('admin.settings.mentorship.create_survey', compact('surveys', 'cycle'));
		} else {
			abort(404);
		}
	}

	public function store_survey($id, Request $request)
	{
		$this->validate($request, [
			'id' => 'exists:cycles,id',
			'survey_id' => 'required|exists:surveys,id',
		]);

		$scope = new SurveyScopes;
		$scope->survey_id = $request->survey_id;
		$scope->cycle_id = $id;
		$scope->organization_id = $request->organization_id;
		$scope->scope = 'mentorship';
		$scope->type = $request->type;
		$scope->trigger_by_session = $request->has('trigger_by_session') ? true : false;
		$scope->trigger_by_due = $request->has('trigger_by_due') ? true : false;
		$scope->trigger_by_quiz = false;
		$scope->save();

		return redirect()->back()->with('success', 'StoredSuccessfully');
	}

	public function edit_survey($id, $survey, Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->find($id);

		if (!empty($cycle)) {
			$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'mentorship')->find($survey);
			if (!empty($cycle)) {
				$organization_id = $cycle->organization_id;
				$surveys = Surveys::where('organization_id', $organization_id)->get();
				return view('admin.settings.mentorship.edit_survey', compact('surveys', 'cycle', 'scope'));
			} else {
				abort(404);
			}
		} else {
			abort(404);
		}
	}

	public function update_survey($id, $survey, Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->find($id);

		if (!empty($cycle)) {
			$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'mentorship')->find($survey);
			if (!empty($cycle)) {
				$scope->type = $request->type;
				$scope->survey_id = $request->survey_id;
				$scope->trigger_by_session = $request->has('trigger_by_session') ? true : false;
				$scope->trigger_by_due = $request->has('trigger_by_due') ? true : false;
				$scope->save();
				return back()->withInput()->with('success', 'StoredSuccessfully');
			} else {
				abort(404);
			}
		} else {
			abort(404);
		}
	}

	public function destroy_survey($id, $survey, Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		$cycle = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->find($id);

		if (!empty($cycle)) {
			$scope = SurveyScopes::where('cycle_id', $cycle->id)->where('scope', 'mentorship')->find($survey);
			if (!empty($cycle)) {
				$scope->delete();
			} else {
				abort(404);
			}
		} else {
			abort(404);
		}
	}

	public function addWait(Request $request)
	{
		$request->validate([
			'notes' => 'nullable|string|max:255',
		]);
		$user = auth()->user();
		if($user->hasRole('admin') || $user->hasRole('organizationManager')){
			
			if ($request->has('selected_dates')) {
				
				$errors = [];
				$successCount = 0;

				foreach ($request->selected_dates as $index => $dateItem) {
				$date = $dateItem['date'];
				$time_from = $dateItem['time_from'];
				$time_to = $dateItem['time_to'];

				$this->meetingService = OnlineMeetingFactory::create();
				$datefrom = $date . ' ' . $time_from;
				$dateto = $date . ' ' . $time_to;

				$mentorshipnotadded = MentorshipRequest::where('zoom_date', $datefrom)->where('mentor_id', $request->mentorid)->first();

				if($mentorshipnotadded){
					$errors[] = "Mentor already scheduled on $date from $time_from to $time_to";
					continue;
				}
				$period = $request->period ?: Carbon::parse($datefrom)->diffInMinutes(Carbon::parse($dateto));
				$dayName = Carbon::parse($date)->format('l');

				$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `days` LIKE ? AND tfrom <= ? AND tto >= ?";
				$schedules = DB::select($schedules_q, [
					$request->mentorid,
					'%' . $dayName . '%',
					$time_from,
					$time_to
				]);

				if ($schedules == null) {
					$errors[] = "Mentor not available on $date from $time_from to $time_to";
					continue;
				}

				if (Carbon::parse($date)->toDateString() < now()->toDateString()) {
					$errors[] = "Cannot schedule meeting in the past: $date";
					continue;
				}

				$fileName = '';
				if ($request->hasFile('file_url')) {
					$fileName = $request->file('file_url')->store($request->user_id, ['disk' => 'PrivateDisk']);
				}

				$cycle = Cycle::find($request->cycle_id);
				
				$autoApprove = $cycle->auto_approve_mentorship_requests;
				$approved = 'Yes';

				$userid = $request->user_id;
				$topic = "Online mentorship meeting (M{$request->mentorid}-U{$userid})";
				$data = ['topic' => $topic, "start_time" => $date, 'duration' => $period, 'host_video' => 1, 'participant_video' => 1];
				$onlineMeeting = $this->meetingService->createMeeting($data);

				if (!$onlineMeeting['success']) {
					$errors[] = "Zoom meeting failed on $date";
					continue;
				}

				$meetingId = $onlineMeeting['data']['id'];
				$startUrl = $onlineMeeting['data']['start_url'];
				$joinUrl = $onlineMeeting['data']['join_url'];
				$passcode = $onlineMeeting['data']['passcode'] ?? null;

				$createMentor = MentorshipRequest::create([
					'mentor_id' => $request->mentorid,
					'user_id' => $userid,
					'comment' => $request->notes,
					'approved' => $approved,
					'start_url' => $startUrl,
					'join_url' => $joinUrl,
					'zoom_id' => $meetingId,
					'zoom_passcode' => $passcode,
					'zoom_date' => $datefrom,
					'period' => $period,
					'organization_id' => $request->organization_id,
					'cycle_id' => $request->cycle_id,
					'fileurl' => $fileName,
				]);

				$successCount++;

				if (!in_array(env('APP_ENV', 'local'), ['local'])) {
					SendMentorshipRequestJob::dispatch($request->mentorid, $date, $request->period, $userid, null, null, 'addwait');
				}
				}
				

				if ($successCount > 0) {
					return back()->with(['message' => "Request_successfully_added_Startup"]);
				} else {
					return back()->with(['message2' => "Error, can't add request", 'errors' => $errors]);
				}
			}

		}else{

				$this->meetingService = OnlineMeetingFactory::create();
				$datefrom = $request->date . ' ' . $request->timeFrom;
				$dateto = $request->date . ' ' . $request->timeTo;
				if ($request->period == '') {
					$period = Carbon::parse($datefrom)->diffInMinutes(Carbon::parse($dateto));
				} else {
					$period = $request->period;
				}
				$dayName = Carbon::parse($request->date)->format('l');

				$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `days` LIKE ?  AND tfrom <= ? AND tto >= ?";
				$schedules = DB::select($schedules_q, [
					$request->mentorid,
					'%' . $dayName . '%',
					$request->timeFrom,
					$request->timeTo
				]);
				if ($schedules == null) {
					return back()->with(['msg' => 'mentor_not_available_in_this_time']);
				}
				if ($request->mentorid) {
					
					if (Carbon::parse($request->date)->toDateString() < now()->toDateString()) {
						return back()->with(['msg' => 'cannot_schedule_past_meeting']);
					}
					$fileName = '';
					if ($request->hasFile('file_url')) {
						$fileName = $request->file('file_url');
						$fileName = $fileName->store(Auth::id(), ['disk' => 'PrivateDisk']);
					}

					$cycle = Cycle::find($request->cycle_id);
					$autoApprove = $cycle->auto_approve_mentorship_requests;

					$approved = null;
					$userid = Auth::id();
					$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $userid . ')';
					$data = ['topic' => $topic, "start_time" => $request->date, 'duration' => $period, 'host_video' => 1, 'participant_video' => 1];
					$onlineMeeting = $this->meetingService->createMeeting($data);
					if (!$onlineMeeting['success']) {
						return back()->with(['msg' => "meeting_error", 'class' => 'danger']);
					}
					$meetingId = $onlineMeeting['data']['id'];
					$startUrl = $onlineMeeting['data']['start_url'];
					$joinUrl = $onlineMeeting['data']['join_url'];
					$passcode = $onlineMeeting['data']['passcode'] ?? null;
					if ($autoApprove == 1) {
						$approved = 'Yes';
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																							
						$dateFormat = new \DateTime($datefrom);
						
						MentorshipRequest::create([
							'mentor_id' => $request->mentorid,
							'user_id' => $userid,
							'comment' => $request->notes,
							'approved' => 'Yes',
							'start_url' => $startUrl,
							'join_url' => $joinUrl,
							'zoom_id' => $meetingId,
							'zoom_passcode' => $passcode,
							'zoom_date' => $dateFormat->format('Y-m-d H:i'),
							'period' => $period,
							'organization_id' => $request->organization_id,
							'cycle_id' => $request->cycle_id,
							'fileurl' => $fileName,
						]);

						$mentor = User::find($request->mentorid);
					} else {
						MentorshipRequest::create([
							'mentor_id' => $request->mentorid,
							'user_id' => Auth::id(),
							'comment' => $request->notes,
							'start_url' => $startUrl,
							'join_url' => $joinUrl,
							'zoom_id' => $meetingId,
							'zoom_date' => $datefrom,
							'period' => $period,
							'fileurl' => $fileName,
							'organization_id' => $request->organization_id,
							'cycle_id' => $request->cycle_id,
							'approved' => $approved,
						]);
					}
					if (!in_array(env('APP_ENV', 'local'), ['local'])) {
						SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), null, null, 'addwait');
						// 
					}

					return back()->with(['msg' => 'Request_successfully_added_Startup', 'class' => 'success']);
				} else {
					return back()->with(['msg' => "Error, can't add request", 'class' => 'danger']);
				}

		}
		
		return back()->with(['msg' => "Error, can't add request", 'class' => 'danger']);
	}


	public function add(Request $request)
	{

		if ($request->userid) {
			$dates = explode(',', $request->date);

			$dateDatabase = MentorshipRequest::whereIn('zoom_date', $dates)->where('mentor_id', $request->mentorid)->where('user_id', $request->userid)->get();
			if (count($dateDatabase) > 0) {
				return back()->with(['message2' => "Cant_add_request_date_is_Exist", 'notAdd' => 2]);
			}
			foreach ($dates as $key) {
				$date = ltrim($key);
				$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $request->userid . ')';
				$data = ['topic' => $topic, "start_time" => $date, 'duration' => $request->period, 'host_video' => 1, 'participant_video' => 1];

				$onlineMeeting = $this->meetingService->createMeeting($data);

				if (!$onlineMeeting['success']) {
					return back()->with(['message2' => "meeting_error"]);
				}
				// $date = substr($date1, 0, -2);
				// $dateAM = substr($date1, -2);
				$dateFormat = new \DateTime($date);
				// dd($dateFormat->format('Y-m-d H:i'));
				MentorshipRequest::create([
					'mentor_id' => $request->mentorid,
					'user_id' => $request->userid,
					'admin_comment' => $request->notes,
					'approved' => 'Yes',
					'admin_id' => Auth::id(),
					'start_url' => $onlineMeeting['data']['start_url'],
					'join_url' => $onlineMeeting['data']['join_url'],
					'zoom_id' => $onlineMeeting['data']['id'],
					'zoom_passcode' => $onlineMeeting['data']['passcode'],
					'zoom_date' => $dateFormat->format('Y-m-d H:i'),
					'period' => $request->period,
					'organization_id' => $request->organization_id,
					'cycle_id' => $request->cycle_id,
				]);


				SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), $onlineMeeting, null, null, 'add');
				/// End of mail send
			}
			return back()->with([
				'message' => 'Session added successfully',
				'add' => 1
			]);
		} else {
			return back()->with([
				'message2' => "Error, can't add session",
				'notAdd' => 2
			]);
		}
	}

	public function sessions(Request $request)
	{
		$organization_id = auth()->user()->organization_id;
		if ($request->show == 'all') {
			 $query = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->when($request->start_date && $request->end_date, function ($q) use ($request) {
				// التأكد من أن التواريخ متوافقة مع التنسيق المناسب
			$startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

				// البحث باستخدام التاريخ من وإلى
				return $q->whereBetween('zoom_date', [$startDate, $endDate]);
			})->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		} elseif ($request->show == 'approved') {
			 $query = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->whereNotNull('start_url')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				$startDate = Carbon::parse($request->start_date)->startOfDay();
				$endDate = Carbon::parse($request->end_date)->endOfDay();
				$q->whereBetween('zoom_date', [$startDate, $endDate]);
			})->where('approved', 'Yes')->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		} elseif ($request->show == 'disapproved') {
			 $query = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->where('approved', 'No')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				$startDate = Carbon::parse($request->start_date)->startOfDay();
				$endDate = Carbon::parse($request->end_date)->endOfDay();
				$q->whereBetween('zoom_date', [$startDate, $endDate]);
			})->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		} else {
			 $query = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->whereNull('approved')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				$startDate = Carbon::parse($request->start_date)->startOfDay();
				$endDate = Carbon::parse($request->end_date)->endOfDay();
				$q->whereBetween('zoom_date', [$startDate, $endDate]);
				return $q->whereDate('zoom_date', '>=', $startDate)->whereDate('zoom_date', '<=', $endDate);
			})->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		}

    	if ($request->filled('done') && $request->done !== 'all') {
         $query->where('approved', 'Yes');
   		 if ($request->done === 'yes') {
        	$query->where('done', 1);
    	} elseif ($request->done === 'no') {
        	$query->whereNull('done');
   		 }
    	}

		$sessions = $query->orderBy('id', 'DESC')->get();

		$sessions = $sessions->map(function ($session) {
			$session->date = Carbon::parse($session->zoom_date);
			return $session;
		});

		if(!is_null($organization_id)) {
			$cycles = \App\Cycle::where('organization_id', $organization_id)->get();
		} else {
				$cycles = \App\Cycle::all();
		}

		return view('admin.settings.mentorship.sessions', compact('sessions', 'cycles'));
	}

	public function getSession(Request $request)
	{
		if ($request) {
			$my = MentorshipRequest::findOrFail($request->id);
			return response()->json($my);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function modifySession(Request $request)
	{

		$request->date = \Carbon\Carbon::createFromFormat('Y-m-d h:i a', $request->date)->format('Y-m-d H:i:s');

		if ($request) {
			$approved = $start = $join = null;
			if ($request->approved) {
				$approved = 'Yes';
				$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $request->userid . ')';
				$data = ['topic' => $topic, "start_time" => $request->date, 'duration' => $request->period, 'host_video' => 1, 'participant_video' => 1];
				$onlineMeeting = $this->meetingService->createMeeting($data);
				if (!$onlineMeeting['success']) {
					return back()->with(['message2' => "Error, can't create Online meeting."]);
				}
				$start = $onlineMeeting['data']['start_url'];
				$join = $onlineMeeting['data']['join_url'];
				$meetingId = $onlineMeeting['data']['id'];
				$passcode = $onlineMeeting['data']['passcode'] ?? null;
			}
			MentorshipRequest::find($request->id)->update(['period' => $request->period, 'zoom_date' => $request->date, 'admin_comment' => $request->notes, 'approved' => $approved, 'zoom_passcode' => $passcode, 'start_url' => $start, 'join_url' => $join, 'zoom_id' => $meetingId]);

			SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), null, $request->id, null, 'modifySession');
			/// Notify Mentor
			/// End of mail send
			return back()->with(['message' => 'Saved_successfully']);
		} else {
			return back()->with(['message2' => 'Error saving data']);
		}
	}

	public function modify(Request $request)
	{
		try {

			$type = $request->rtype;
			if ($type == 6) {

				$start = $join = null;
				if ($request->stat == 'Yes') {

					$mRequest = MentorshipRequest::findOrFail($request->id);
					$topic = "Online mentorship meeting (M" . $mRequest->mentor_id . '- U' . $mRequest->user_id . ')';
					$data = ['topic' => $topic, "start_time" => $mRequest->zoom_date, 'duration' => $mRequest->period, 'host_video' => 1, 'participant_video' => 1];
					
				}


				MentorshipRequest::find($request->id)->update(['approved' => $request->stat]);
				$locale = app()->getLocale() ?? 'en';
				/// Notify Mentor
				SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), null, $request->id, $request->stat, 'modify');


				return response()->json('success');
			} elseif ($type == 5) {
				$my = Schedule::where('active', 1)->where('user_id', $request->id)->get();
				// $sessions = MentorshipRequest::where('mentor_id', $request->id)
				// 	->where('zoom_date', '>=', date("Y-m-d 00:00:00"))
				// 	->where('approved', 'Yes')
				// 	->get(['zoom_date as dfrom', 'period AS tfrom', DB::raw("(id + 1000000) AS id, 'Session' AS type, 'Mentorship session' AS notes")]);
				
					// $my = Schedule::where('active', 1)->where('user_id', $request->id)->get();
				// $sessions = MentorshipRequest::where('mentor_id', $request->id)
				// 	->where('zoom_date', '>=', date("Y-m-d 00:00:00"))
				// 	->where('approved', 'Yes')
				// 	->get(['zoom_date as dfrom', 'period AS tfrom', DB::raw("(id + 1000000) AS id, 'Session' AS type, 'Mentorship session' AS notes")]);

                    $sessions = MentorshipRequest::where('mentor_id', $request->id)
						->where('zoom_date', '>=', date("Y-m-d 00:00:00"))
						->where('approved', 'Yes')
						->get([
							DB::raw("DATE(zoom_date) AS date"),
							DB::raw("TIME(zoom_date) AS tfrom"),
							DB::raw("TIME(ADDTIME(zoom_date, SEC_TO_TIME(period * 60))) AS tto"),
							DB::raw("(id + 1000000) AS id"),
							DB::raw("'Session' AS type"),
							DB::raw("'Mentorship session' AS notes")
						]);
			// $my = $my->merge($sessions);
				return response()->json($my);
			} elseif ($type == 4) {
				$values = ['type' => $request->type, 'days' => json_encode($request->days), 'dfrom' => $request->dfrom, 'dto' => $request->dto, 'tfrom' => $request->tfrom, 'tto' => $request->tto, 'notes' => $request->notes];
				Schedule::where('user_id', Auth::id())->where('id', $request->id)->update($values);
				return back()->with(['msg' => 'Saved', 'class' => 'success']);
			} elseif ($type == 3) {
				Schedule::where('user_id', Auth::id())->where('id', $request->id)->delete();
				return response()->json(['state' => 'success']);
			} elseif ($type == 2) {
				$stat = $request->stat ? 0 : 1;
				Schedule::where('user_id', Auth::id())->where('id', $request->id)->update(['active' => $stat]);
				return response()->json(['state' => 'success']);
			} elseif ($type == 1) {
				$my = Schedule::where('user_id', Auth::id())->get();
				return response()->json($my);
			} else {

				Schedule::where('user_id', Auth::id())->delete();
				// Schedule::create($request->all() + ['user_id' => Auth::id()]);
				$userId = Auth::id();
				$periods = $request->input('periods');

				foreach ($periods as $day => $times) {
					// التحقق من وجود بيانات "from" و "to"
					if (isset($times['from']) && isset($times['to'])) {
						foreach ($times['from'] as $index => $from) {
							$to = $times['to'][$index] ?? null; // تأكد من وجود القيمة المقابلة في "to"

							// تجاهل إذا كانت القيم فارغة
							if (!is_null($from) && !is_null($to)) {
								Schedule::create([
									'user_id' => $userId,
									'type' => $request->input('type'),
									'days' => $day,
									'tfrom' => $from,
									'tto' => $to,
									'notes' => $request->input('notes'),

								]);
							}
						}
					}
				}
				return back()->with(['msg' => 'Saved', 'class' => 'success']);
			}
		} catch (Exception $e) {
			return response()->json(['state' => 'failed', 'e' => $e], 500);
		}
	}

	public function mine()
	{
		$sessions = MentorshipRequest::where('user_id', Auth::id())->orderby('created_at', 'DESC')->get();
		return view('admin.clients.mymentorships', compact('sessions'));
	}

	public function rate(Request $request)
	{
		if ($request) {
			$done = $request->done ? 1 : null;
			MentorshipRequest::where('id', $request->id)->where('user_id', Auth::id())->update(['rating' => $request->rate, 'rate_comment' => $request->comment, 'done' => $done]);
			return back();
		} else {
			return back();
		}
	}



	public function delSession($id, Request $request)
	{
		
		if ($request) {
			
			MentorshipRequest::findOrFail($id)->delete();
			return response()->json(['state' => 'success'], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	

	
}
