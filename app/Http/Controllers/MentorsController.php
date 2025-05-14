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
use Illuminate\Support\Facades\DB;
use App\Services\WhatsAppService;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use App\Factories\OnlineMeetingFactory;
use Illuminate\Support\Facades\Log;
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

		if($request->step_id != 0){
			$companies = Company::where('cycle', $id)
    ->where('step', '>', 0)
    ->when($request->has('step_id'), function ($query) use ($request) {
        return $query->where('step', $request->step_id);
    })
    ->get();
		}

		
		return view('admin.settings.mentorship.index', compact('mentors', 'id', 'companies', 'cycles'));
	}

	public function list(Request $request, $id = 0 )
	{
		$l = app()->getLocale();
		$organization_id = auth()->user()->organization_id;
		if ($id == 0) {
			$id = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->orderBy('start', 'DESC')->pluck('id')->first();
		}
		$mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
		$cList = CyclesMentors::where('cycle_id', $id)->pluck('user_id')->toArray();
		$cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->where('organization_id', $organization_id);
		})->get();

		$selectedSpecialization = $request->specialization; // الحصول على قيمة التصفية من query string
   
		if ($selectedSpecialization != 0) {
			$mentors = $mentors->filter(function ($mentor) use ($selectedSpecialization, $l) {
				$mentorSpecialization = $mentor->bio->{'specialization_'.$l} ?? null;
		
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
			CyclesMentors::where('cycle_id', $request->id)->delete();
			if ($request->users) {
				foreach ($request->users as $user) {
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
		})->find($id);

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
			return view('admin.settings.mentorship.surveys', compact('surveys', 'cycle'));
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

		}else{
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
		
		$datefrom = $request->date .' '. $request->timeFrom;
		$dateto = $request->date .' '. $request->timeTo;
		if($request->period == ''){
			$period = Carbon::parse($datefrom)->diffInMinutes(Carbon::parse($dateto));
		}else{
			$period = $request->period;
		}

		if ($request->mentorid) {
			if ($request->date < date("Y-m-d 00:00:00")) {
				return back()->with(['msg' => "Error, can't add late date.", 'class' => 'danger']);
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
			 if ($autoApprove == 1) {
				
				 $approved = 'Yes';
				 $topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $userid . ')';
				 $data = ['topic' => $topic, "start_time" => $request->date, 'duration' => $period, 'host_video' => 1, 'participant_video' => 1];
				
				$onlineMeeting = $this->meetingService->createMeeting($data);
			
				if (!$onlineMeeting['success']) {
					return back()->with(['msg' => "meeting_error", 'class' => 'danger']);
				}
				
				// $date = substr($date1, 0, -2);
				// $dateAM = substr($date1, -2);																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																		
				$dateFormat = new \DateTime($request->date);
				// dd($dateFormat->format('Y-m-d H:i'));
				MentorshipRequest::create([
					'mentor_id' => $request->mentorid,
					'user_id' => $userid,
					'comment' => $request->notes,
					'approved' => 'Yes',
					'start_url' => $onlineMeeting['data']['start_url'],
					'join_url' => $onlineMeeting['data']['join_url'],
					'zoom_date' => $dateFormat->format('Y-m-d H:i'),
					'period' => $period,
					'organization_id' => $request->organization_id,
					'cycle_id' => $request->cycle_id,
					'fileurl' => $fileName,
				]);
				
				$mentor = User::find($request->mentorid);
			}else{
			
			MentorshipRequest::create([
				'mentor_id' => $request->mentorid,
				'user_id' => Auth::id(),
				'comment' => $request->notes,
				'zoom_date' => $datefrom,
				'period' => $period,
				'fileurl' => $fileName,
				'organization_id' => $request->organization_id,
				'cycle_id' => $request->cycle_id,
				'approved' => $approved,
			]);
		}
			if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
			$data = [];
			$mentor = User::find($request->mentorid);
			// ======> notify Mentor <=================
				$data['Mentorname'] = $mentor->first_name_en . " " . $mentor->last_name_en;
				$data['email'] = $mentor->email;
				$data['date'] = $request->date;
				$data['period'] = $request->period;
				$data['user_name'] = Auth::user()->first_name_en . " " . Auth::user()->last_name_en;
				$data['company_name'] = Auth::user()->company->name_en;
				try {
					$mentor->notify(new MentorshipRequestNotification($data));
					log::info('Sent New Mentorship Request notification to ' . $mentor->email);
				} catch (\Throwable $th) {
					throw $th;
				}
				$phoneNumber = $mentor->country_code_w_a . $mentor->whatsapp;
				$Massage = "Dear " . $mentor->name . ",
 '" . $data['company_name'] . "' has sent you a mentorship request,
request will start: " . $data['date'] . ",
Thanks from " . env('APP_NAME');
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
		}
			// Notify Admin here
			event(new WebMessage('NewMent', Auth::user()->first_name_en . ' ' . Auth::user()->last_name_en));
			return back()->with(['msg' => 'Request_successfully_added_Startup', 'class' => 'success']);
		} else {
			return back()->with(['msg' => "Error, can't add request", 'class' => 'danger']);
		}
	}

	public function add(Request $request)
	{

		if ($request->userid) {
			$dates = explode(',', $request->date);
			
			$dateDatabase = MentorshipRequest::whereIn('zoom_date', $dates)->where('mentor_id', $request->mentorid)->where('user_id', $request->userid)->get();
			if(count($dateDatabase) > 0) {
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
					'zoom_date' => $dateFormat->format('Y-m-d H:i'),
					'period' => $request->period,
					'organization_id' => $request->organization_id,
					'cycle_id' => $request->cycle_id,
				]);
				/// notify user via Mail
				$mentor = User::findOrFail($request->mentorid);
				$mentor_name = $mentor->name;

				$startup_owner = User::findOrFail($request->userid);
				$startup_owner_name = $startup_owner->name;
				/// Notify Mentor
				$user = new User();
				$user->email = $mentor->email;
				$user->id = $mentor->id;
				$MSG = new TemplateEmail;
				$MSG->Message = [
					"New Mentorship session - VI portal",
					"Dear " . $mentor_name . ",",
					"Kindly note you have a new mentorship session with '" . $startup_owner_name . "', Session will start:" . $date . ", Check your account to join the session ",
					"/dashboard"
				];
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					try {
						$user->notify($MSG);
					} catch (\Throwable $th) {
						// throw $th;
					}
					$phoneNumber = $startup_owner->country_code_w_a . $startup_owner->whatsapp;
						$Massage = "Dear " . $mentor_name . ", 
This Is notification new mentorship session with '" . $startup_owner_name . "',
Session will start:" . $date . ",
Check your account to join the session ";
						$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
						Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
				}
				/// Notify Startup Owner
				$user = new User();
				$user->email = $startup_owner->email;
				$user->id = $startup_owner->id;
				$MSG = new TemplateEmail;
				$MSG->Message = [
					"New Mentorship session - VI portal",
					"Dear " . $startup_owner_name . ",",
					"Kindly note you have a new mentorship session with '" . $mentor_name . "', Session will start:" . $date . ", Check your account to join the session ",
					"/dashboard"
				];
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					try {
						$user->notify($MSG);
					} catch (\Throwable $th) {
						// throw $th;
					}
					$phoneNumber = $startup_owner->country_code_w_a . $startup_owner->whatsapp;
					$Massage = "Dear " . $startup_owner->name . ",
This Is notification new mentorship session with '" . $startup_owner_name . "',
Session will start: " . $date . ",
Session Link: " . $onlineMeeting['data']['join_url']. ",
Thanks from " . env('APP_NAME');
					$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
					Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
				}
				
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
			$sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->when($request->start_date && $request->end_date, function ($q) use ($request) {
				// التأكد من أن التواريخ متوافقة مع التنسيق المناسب
				$startDate = date('Y-m-d', strtotime($request->start_date));
				$endDate = date('Y-m-d', strtotime($request->end_date));

				// البحث باستخدام التاريخ من وإلى
				return $q->whereDate('zoom_date', '>=', $startDate)->whereDate('zoom_date', '<=', $endDate);
			})->when(!is_null($request->cycle_id), function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			})->orderBy('id', 'DESC')->get();

		} elseif ($request->show == 'approved') {
			$sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->whereNotNull('start_url')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				// التأكد من أن التواريخ متوافقة مع التنسيق المناسب
				$startDate = date('Y-m-d', strtotime($request->start_date));
				$endDate = date('Y-m-d', strtotime($request->end_date));

				// البحث باستخدام التاريخ من وإلى
				return $q->whereDate('zoom_date', '>=', $startDate)->whereDate('zoom_date', '<=', $endDate);
			})->where('approved', 'Yes')->when(!is_null($request->cycle_id), function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			})->orderBy('id', 'DESC')->get();

		} elseif ($request->show == 'disapproved') {
			$sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->where('approved', 'No')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				// التأكد من أن التواريخ متوافقة مع التنسيق المناسب
				$startDate = date('Y-m-d', strtotime($request->start_date));
				$endDate = date('Y-m-d', strtotime($request->end_date));

				// البحث باستخدام التاريخ من وإلى
				return $q->whereDate('zoom_date', '>=', $startDate)->whereDate('zoom_date', '<=', $endDate);
			})->when(!is_null($request->cycle_id), function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			})->orderBy('id', 'DESC')->get();
		} else {
			$sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->where('organization_id', $organization_id);
			})->whereNull('approved')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				// التأكد من أن التواريخ متوافقة مع التنسيق المناسب
				$startDate = date('Y-m-d', strtotime($request->start_date));
				$endDate = date('Y-m-d', strtotime($request->end_date));
				// البحث باستخدام التاريخ من وإلى
				return $q->whereDate('zoom_date', '>=', $startDate)->whereDate('zoom_date', '<=', $endDate);
			})->when(!is_null($request->cycle_id), function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			})->orderBy('id', 'DESC')->get();
		}
		$sessions = $sessions->map( function( $session ) {
			$session->date = Carbon::parse($session->zoom_date);
			return $session;
		});
		return view('admin.settings.mentorship.sessions', compact('sessions'));
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
			}
			MentorshipRequest::find($request->id)->update(['period' => $request->period, 'zoom_date' => $request->date, 'admin_comment' => $request->notes, 'approved' => $approved, 'start_url' => $start, 'join_url' => $join]);
			/// Notify Mentor
			$session = MentorshipRequest::findOrFail($request->id);
			$rName = User::findOrFail($session->mentor_id);
			$rName_usr = User::findOrFail($session->user_id);
			$user = new User();
			$user->email = $rName->email;
			$user->id = $rName->id;
			$rFullName = $rName->name;
			$rFullName_usr = $rName_usr->name;
			$MSG = new TemplateEmail;
			$MSG->Message = [
				"New Mentorship session - VI portal",
				"Dear " . $rFullName . ",",
				"Kindly note you have a new mentorship session with '" . $rFullName_usr . "', Session will start:" . $request->date . ", Check your account to join the session ",
				"/dashboard"
			];
            if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
				$phoneNumber = $rName->country_code_w_a . $rName->whatsapp;
				$Massage = "Dear " . $rName->name . ",
you have a new mentorship session with '" . $rFullName . "',
Session will start:" . $request->date . ",
Check your account to join the session,
Thanks from " . env('APP_NAME');
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
			/// Notify User
			$user = new User();
			$user->email = $rName_usr->email;
			$user->id = $rName_usr->id;
			$MSG = new TemplateEmail;
			$MSG->Message = [
				"New Mentorship session - VI portal",
				"Dear " . $rFullName_usr . ",",
				"Kindly note you have a new mentorship session with '" . $rFullName . "', Session will start:" . $request->date . ", Check your account to join the session ",
				"/dashboard"
			];
            if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
				$phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
				$Massage = "Dear " . $rName_usr->name . ",
you have a new mentorship session with '" . $rFullName . "',
Session will start:" . $request->date . ",
Check your account to join the session,
Thanks from " . env('APP_NAME');
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
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
					$onlineMeeting = $this->meetingService->createMeeting($data);

					if (!$onlineMeeting['success']) {
						return response()->json('failed, Online meeting api error');
					}
					$start = $onlineMeeting['data']['start_url'];
					$join = $onlineMeeting['data']['join_url'];
				}
				
				MentorshipRequest::find($request->id)->update(['approved' => $request->stat, 'start_url' => $start, 'join_url' => $join]);
				/// Notify Mentor
				$session = MentorshipRequest::findOrFail($request->id);
				$rName = User::findOrFail($session->mentor_id);
				$rName_usr = User::findOrFail($session->user_id);
				$user = new User();
				$user->email = $rName->email;
				$user->id = $rName->id;
				$date_Request = $session->zoom_date;
				$rFullName = $rName->name;
				$rFullName_usr = $rName_usr->name;
				$MSG = new TemplateEmail;
				if ($request->stat == 'Yes') {
				$MSG->Message = [
					"New Mentorship session - VI portal",
					"Dear " . $rFullName . ",",
					"Kindly note you have a new mentorship session with '" . $rFullName_usr . "', Session will start: " . $date_Request . ", Check your account to join the session ",
					"/dashboard"
				];
				$phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
					$Massage = "Dear " . $rFullName . ",
you have a new mentorship session with '" . $rFullName_usr . "',
Session will start:" . $date_Request . ",
Check your account to join the session,
Thanks from " . env('APP_NAME');

			}else{
				$MSG->Message = [
					"New Mentorship session - VI portal",
					"Dear " . $rFullName . ",",
					"Kindly note you have a new mentorship session with '" . $rFullName_usr . "', is Rejected",
					"/dashboard"
				];
				$phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
					$Massage = "Dear " . $rFullName . ",
you have a new mentorship session with '" . $rFullName_usr . "',
is Rejected
Thanks from " . env('APP_NAME');
			}
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					try {
						$user->notify($MSG);
					} catch (\Throwable $th) {
						// throw $th;
					}
					$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
					Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
				}
				/// Notify User
				$user = new User();
				$user->email = $rName_usr->email;
				$user->id = $rName_usr->id;
				$MSG = new TemplateEmail;
				if ($request->stat == 'Yes') {
				$MSG->Message = [
					"New Mentorship session - VI portal",
					"Dear " . $rFullName_usr . ",",
					"Kindly note you have a new mentorship session with '" . $rFullName . "', Session will start: " . $date_Request . ", Check your account to join the session ",
					"/dashboard"
				];
				$phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
					$Massage = "Dear " . $rFullName_usr . ",
you have a new mentorship session with '" . $rFullName . "',
Session will start:" . $date_Request . ",
Check your account to join the session,
Thanks from " . env('APP_NAME');
			}else{
					$MSG->Message = [
						"New Mentorship session - VI portal",
						"Dear " . $rFullName_usr . ",",
						"Kindly note you have a new mentorship session with '" . $rFullName . "', is Rejected",
						"/dashboard"
					];
					$phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
					$Massage = "Dear " . $rFullName_usr . ",
you have a new mentorship session with '" . $rFullName . "',
is Rejected
Thanks from " . env('APP_NAME');
				}
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					try {
						$user->notify($MSG);
					} catch (\Throwable $th) {
						// throw $th;
					}
					$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
					Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
				}
				/// End of mail send
				return response()->json('success');
			} elseif ($type == 5) {
				$my = Schedule::where('active', 1)->where('user_id', $request->id)->get();
				$sessions = MentorshipRequest::where('mentor_id', $request->id)
					->where('zoom_date', '>=', date("Y-m-d 00:00:00"))
					->where('approved', 'Yes')
					->get(['zoom_date as dfrom', 'period AS tfrom', DB::raw("(id + 1000000) AS id, 'Session' AS type, 'Mentorship session' AS notes")]);
				$my = $my->merge($sessions);
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
									'type'=> $request->input('type'),
									'days' => $day,
									'tfrom' => $from,
									'tto' => $to,
									'notes'=> $request->input('notes'),
									
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
		// $zoomId = TrainingSession::findOrFail($id)->zoom_id;
		if ($request)
		{
			// if( $zoomId )
			// {
			// 	 $zoom = $this->delete( $zoomId );
			// 	 if( !$zoom['success'] ) { return response()->json(['state' => 'failed'], 500); }
			// }
			MentorshipRequest::findOrFail($id)->delete();
			return response()->json(['state' => 'success'], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	// public function MentorDates(Request $request)
	// {
	// 		$mentor_id = $request->mentor_id;

	// 		$dates  = "Select mentor_id, zoom_date, user_id from mentorship_requests where mentor_id = $mentor_id AND approved = 'Yes' ";
	// 		$dates = DB::select($dates);
	// 		return response()->json(['dates' => $dates]);
		
	// }
}
