<?php

namespace App\Http\Controllers;

use App\AttendanceTrainingOffline;
use App\Company;
use App\Cycle;
use App\Organization;
use App\CyclesMentors;
use App\Events\MentorshipSessionUpdated;
use App\Events\RequestMentorship;
use App\Events\WebMessage;
use App\Factories\OnlineMeetingFactory;
use App\Http\Resources\MentorResource;
use App\Jobs\ExportMentorsData;
use App\Jobs\SendMentorshipNotification;
use App\Jobs\SendMentorshipRequestJob;
use App\MentorshipRequest;
use App\Notifications\MentorshipRequestNotification;
use App\Notifications\MentorshipSession;
use App\Notifications\MentorshipSessionFounderBookingNotification;
use App\Notifications\TemplateEmail;
use App\Schedule;
use App\Services\TimezoneService;
use App\Services\WhatsAppService;
use App\Settings;
use App\SurveyQuestions;
use App\SurveyResponses;
use App\Surveys;
use App\SurveyScopes;
use App\User;
use App\Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;
use App\Notifications\MentorshipSessionRequestNotification;
use App\Notifications\MentorshipSessionMentorBookingNotification;
use App\Notifications\MentorshipSessionFounderNotification;

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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		if ($id == 0) {
			$id = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->orderBy('start', 'DESC')->pluck('id')->first();
		}
		$companies = Company::where('cycle', $id)->where('step', '>', 0)->get();
		$mentors = CyclesMentors::where('cycle_id', $id)->get();
		$cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
			
			$user = auth()->user();
			$orgIds = $user->organizations_ids;
			if ($id == 0) {
					
					$id = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
							return $q->whereIn('organization_id', $orgIds);
					})->orderBy('start', 'DESC')->pluck('id')->first();
			}
			// $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
			$mentors = User::role('mentor')->with('inviteMentors', 'mentorExpertises');
			
			$cList = CyclesMentors::where('cycle_id', $id)->pluck('user_id')->toArray();


			// $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			// 	return $q->whereIn('organization_id', $orgIds);
			// })->get();

			$scopeFilter = $request->scopeFilter;

			// Old scope filter logic (commented out — replaced by new logic below)
			// if (!$user->hasRole('Admin') && !empty($orgIds)) {
			// 		$cycles = Cycle::whereIn('organization_id', $orgIds)->get();
			// 		if (!empty($scopeFilter) && $scopeFilter !== 'all') {
			// 				if ($scopeFilter === 'public') {
			// 						$mentors->where(function ($q) {
			// 								$q->whereDoesntHave('inviteMentors')
			// 								->orWhere(function ($q2) {
			// 										$q2->whereHas('inviteMentors', function ($q3) {
			// 												$q3->where('scope', 'Public')
			// 														->where('status', 'accepted');
			// 										});
			// 								});
			// 						});
			// 				} else {
			// 						$mentors->whereHas('inviteMentors', function ($query) use ($scopeFilter, $request) {
			// 								$query->where('status', 'accepted');
			// 								if ($request->scopeFilter != null) {
			// 										$query->whereJsonContains('organization_ids', (string) $request->scopeFilter);
			// 								}
			// 						});
			// 				}
			// 		}
			// } else {
			// 		$cycles = Cycle::all();
			// 		if (!empty($scopeFilter) && $scopeFilter !== 'all') {
			// 				if ($scopeFilter === 'public') {
			// 						$mentors->where(function ($q) {
			// 								$q->whereDoesntHave('inviteMentors')
			// 								->orWhere(function ($q2) {
			// 										$q2->whereHas('inviteMentors', function ($q3) {
			// 												$q3->where('scope', 'Public')
			// 														->where('status', 'accepted');
			// 										});
			// 								});
			// 						});
			// 				} else {
			// 						$mentors->whereHas('inviteMentors', function ($query) use ($scopeFilter, $request) {
			// 								$query->where('status', 'accepted');
			// 								if ($request->scopeFilter != null) {
			// 										$query->whereJsonContains('organization_ids', (string) $request->scopeFilter);
			// 								}
			// 						});
			// 				}
			// 		}
			// }

			if (!empty($scopeFilter) && $scopeFilter !== 'all') {
					if ($scopeFilter === 'public') {
							// Public: no invitations OR has an accepted Public-scope invitation
							$mentors = $mentors->where(function ($q) {
									$q->whereDoesntHave('inviteMentors')
									  ->orWhereHas('inviteMentors', function ($q2) {
											$q2->where('scope', 'Public')->where('status', 'accepted');
									  });
							});
					} else {
							// Specific org: must have an accepted invitation with this org in organization_ids
							$mentors = $mentors->whereHas('inviteMentors', function ($query) use ($scopeFilter) {
									$query->where('status', 'accepted')
									      ->whereJsonContains('organization_ids', (string) $scopeFilter);
							});
					}
			} else {
					// scopeFilter = all (or empty)
					// Old logic (commented out): used user's orgIds + cycleMentorIds
					// if (!$user->hasRole('Admin') && !empty($orgIds)) {
					// 		$cycleIds = Cycle::whereIn('organization_id', $orgIds)->pluck('id')->toArray();
					// 		$cycleMentorIds = CyclesMentors::whereIn('cycle_id', $cycleIds)->pluck('user_id')->toArray();
					// 		$mentors = $mentors->where(function ($q) use ($cycleMentorIds) {
					// 				$q->whereIn('id', $cycleMentorIds)
					// 				  ->orWhere(function ($q2) {
					// 						$q2->whereDoesntHave('inviteMentors')
					// 						  ->orWhereHas('inviteMentors', function ($q3) {
					// 								$q3->where('scope', 'Public')->where('status', 'accepted');
					// 						  });
					// 				  });
					// 		});
					// }

					// New logic: restrict to this cycle's organization + public mentors
					$cycle = Cycle::find($id);
					$cycleOrgId = $cycle ? $cycle->organization_id : null;

					if ($cycleOrgId) {
							$mentors = $mentors->where(function ($q) use ($cycleOrgId) {
									// Public mentors
									$q->where(function ($q2) {
											$q2->whereDoesntHave('inviteMentors')
											  ->orWhereHas('inviteMentors', function ($q3) {
													$q3->where('scope', 'Public')->where('status', 'accepted');
											  });
									})
									// OR mentors belonging to this cycle's organization
									->orWhereHas('inviteMentors', function ($q2) use ($cycleOrgId) {
											$q2->where('status', 'accepted')
											   ->whereJsonContains('organization_ids', (string) $cycleOrgId);
									});
							});
					}
			}

			if (!$user->hasRole('Admin') && !empty($orgIds)) {
					$cycles = Cycle::whereIn('organization_id', $orgIds)->get();
			} else {
					$cycles = Cycle::all();
			}
			
			$mentors = $mentors->get()->map(function ($mentor) {
					// افتراضيًا public لو مفيش invites
					$mentorScope = 'public';
					$mentorOrgIds = [];

					if ($mentor->inviteMentors && $mentor->inviteMentors->count() > 0) {
							// هنجيب كل invites المقبولة
							$acceptedInvites = $mentor->inviteMentors->filter(fn($i) => strtolower($i->status) === 'accepted');
							
							// لو فيه invite scopes مختلفة، ممكن تختار الأولى أو حسب منطقك
							if ($acceptedInvites->count() > 0) {
									$mentorOrgIds = $acceptedInvites
											->flatMap(fn($invite) => $invite->organization_ids ?? [])
											->unique()
											->values()
											->toArray();
									
									// FIX: Use lowercase 'organization' to match frontend
									$mentorScope = count($mentorOrgIds) > 0 ? 'organization' : strtolower($acceptedInvites->first()->scope);
							}
					}

					// إضافة الخصائص للـ mentor
					// FIX: Return lowercase scope to match frontend expectations
					$mentor->scope = strtolower($mentorScope);
					
					// FIX: Ensure org IDs are stored as integers in JSON
					$mentor->scope_org_ids = json_encode(
							collect($mentorOrgIds)->map(fn($id) => intval($id))->values()->toArray()
					);
					
					return $mentor;
			});
					
			$selectedSpecialization = $request->specialization; 

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

			// $mentors = $mentors->map(function ($mentor) {
			// 	$scopes = $mentor->inviteMentors->pluck('scope')->unique()->values();

			// 	if ($scopes->isEmpty()) {
			// 		$mentor->scope = null;
			// 	} elseif ($scopes->count() > 1) {
			// 		$mentor->scope = 'all';
			// 	} else {
			// 		$mentor->scope = $scopes->first(); // public أو organization
			// 	}

			// 	return $mentor;
			// });

			// تصفية حسب حالة الـ checked
			if ($request->assigning != 0) {
					$checkedStatus = $request->assigning; // القيمة ستكون "checked" أو "unchecked"

					$mentors = $mentors->filter(function ($mentor) use ($checkedStatus, $cList) {
							$isChecked = in_array($mentor->id, $cList);
							if ($checkedStatus == 'checked') {
									return $isChecked;
							}
							elseif ($checkedStatus == 'Notchecked') {
									return !$isChecked;
							}
							return true; // في حالة عدم تحديد أي فلتر
					});
			}
			
			$settings = Settings::where('id', 1)->first();
			$offline_allowed = $settings->allow_offline_sessions == 1 ? true : false;

			return view('admin.settings.mentorship.assign', compact('mentors', 'id', 'cList', 'cycles', 'offline_allowed'));
	}

	public function assign($id, Request $request)
	{
			if ($request->id) {

					$users = array_filter(explode(',', $request->selected_mentors));

					CyclesMentors::where('cycle_id', $request->id)->delete();

					foreach ($users as $user) {
							CyclesMentors::insert([
									'cycle_id' => $request->id,
									'user_id' => (int) $user,
							]);
					}
			}

			return back()->with(['message' => 'mentors_assigned_successfully', 'class' => 'success']);
	}


	public function list_surveys($id, Request $request)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
			if(Auth::user()->hasAnyRole('organizationManager|subadmin')) {
				$available_surveys = Surveys::where('organization_id', $organization_id)->get();
			}
			if(Auth::user()->hasRole('admin')) {
				$available_surveys = Surveys::all();
			}
			

			return view('admin.settings.mentorship.surveys', compact('surveys', 'cycle', 'available_surveys'));
		} else {
			abort(404);
		}
	}

	public function total_surveys()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		if (Auth::user()->hasAnyRole('organizationManager|subadmin')) {
			$surveys = SurveyScopes::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereIn('organization_id', $orgIds);
			})->with(['survey', 'responses'])->get();
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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$cycle = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereIn('organization_id', $orgIds);
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
		$settings = Settings::where('id', 1)->first();
		
		$request->validate([
			'notes' => 'nullable|string|max:1000',
			'file_url' => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,png,jpeg,jpg',
			'selected_dates' => 'nullable|array',
		], [
			'file_url.mimes' => __('mentor.unsupported_slot_attachment_format'),
			'file_url.max'   => __('mentor.file_excced_10'),
		]);
		$user = auth()->user();
		$getMentor = User::find($request->mentorid);
		$getStartup = User::with(['company', 'company.cycledata'])->find($request->user_id);
		$requestedMentor = $getMentor->name;
		$requestNotes = $request->input('notes') ?: null;
		$requestDate = $request->date;
		$requestTimeFrom = $request->timeFrom;
		$requestTimeTo = $request->timeTo;
		$diffInMinutes = Carbon::parse($requestDate . ' ' . $requestTimeFrom)->diffInMinutes(Carbon::parse($requestDate . ' ' . $requestTimeTo));
		$request_date = Carbon::parse( $requestDate )->translatedFormat('Y-m-d');
		$request_start_time = Carbon::parse($requestTimeFrom)->translatedFormat('H:i');
		$request_end_time = Carbon::parse($requestTimeTo)->translatedFormat('H:i');
		$requestDateTime = __('email.mentorship_request_email.date_time_formatted', [
			'date' => $request_date,
			'start_time' => $request_start_time,
			'end_time' => $request_end_time,
			'duration' => $diffInMinutes,
		]);

		$dateTimePayload = [
			'date' => $request_date,
			'start_time' => $request_start_time,
			'end_time' => $request_end_time,
			'message' => $requestDateTime,
		];

		// The booking modal submits via AJAX and expects a structured JSON
		// summary (per-slot outcomes); legacy plain-form posts still get the
		// redirect + flash behaviour.
		$wantsJson = $request->ajax() || $request->wantsJson();

		if($user->hasAnyRole(['admin', 'organizationManager', 'subadmin']))
		{
			$cycle = $getStartup?->company?->cycledata;
			if ($request->has('selected_dates')) {

				// Only one session type may be booked per time range. The modal
				// disables conflicting slots, but re-check here so a crafted
				// payload cannot double-book the same range.
				$seenRanges = [];
				foreach ($request->selected_dates as $dateItem) {
					$rangeKey = ($dateItem['date'] ?? '') . '|' . ($dateItem['time_from'] ?? '') . '|' . ($dateItem['time_to'] ?? '');
					if (isset($seenRanges[$rangeKey])) {
						if ($wantsJson) {
							return response()->json([
								'status'    => 'error',
								'booked'    => 0,
								'requested' => count($request->selected_dates),
								'failed'    => [],
								'message'   => __('mentor.duplicate_slot_selected'),
							], 200);
						}
						return back()->with(['class' => 'danger', 'msg' => 'duplicate_slot_selected']);
					}
					$seenRanges[$rangeKey] = true;
				}

				$errors = [];
				$failedSlots = [];
				$successCount = 0;
				$requestedCount = count($request->selected_dates);
				$bookedSessions = [];

				// Record a per-slot failure once, for both the JSON summary and
				// the legacy flash ($errors) path.
				$recordFailure = function ($date, $timeFrom, $timeTo, $reason) use (&$errors, &$failedSlots) {
					$errors[] = trim("$reason ($date $timeFrom-$timeTo)");
					$failedSlots[] = [
						'date'      => $date,
						'time_from' => $timeFrom,
						'time_to'   => $timeTo,
						'reason'    => $reason,
					];
				};

				foreach ($request->selected_dates as $index => $dateItem)
				{
					$date = $dateItem['date'];
					$time_from = $dateItem['time_from'];
					$time_to = $dateItem['time_to'];
					$slot_session_type = $dateItem['session_type'] ?? $request->session_type;

					$this->meetingService = OnlineMeetingFactory::create();
					$datefrom = $date . ' ' . $time_from;
					$dateto = $date . ' ' . $time_to;

					$mentorshipnotadded = MentorshipRequest::where('zoom_date', $datefrom)->where('mentor_id', $request->mentorid)->first();

					if($mentorshipnotadded){
						$recordFailure($date, $time_from, $time_to, __('mentor.slot_already_requested'));
						continue;
					}
					
					$period = $request->period ?: Carbon::parse($datefrom)->diffInMinutes(Carbon::parse($dateto));

					// GV-9566: the slot is platform wall-clock; pass the instant (in that zone)
					// so both the timestamp and the wall-clock comparisons in the check agree.
					$slotZone = TimezoneService::platform($request->organization_id ? (int) $request->organization_id : null);
					if (\App\Settings::hasReachedMaxConcurrentSessions(Carbon::parse($datefrom, $slotZone), $period)) {
						$recordFailure($date, $time_from, $time_to, __('api.max_concurrent_sessions_reached'));
						continue;
					}

					$dayName = Carbon::parse($date)->format('l');

					$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `date` LIKE ? AND tfrom <= ? AND tto >= ?";
					$schedules = DB::select($schedules_q, [
						$request->mentorid,
						'%' . $date . '%',
						$time_from,
						$time_to
					]);

					if ($schedules == null) {
						$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `days` LIKE ? AND tfrom <= ? AND tto >= ?";
						$schedules = DB::select($schedules_q, [
							$request->mentorid,
							'%' . $dayName . '%',
							$time_from,
							$time_to
						]);
					}

					if ($schedules == null) {
						$recordFailure($date, $time_from, $time_to, __('mentor.mentor_not_available_in_this_time'));
						continue;
					}
					if($slot_session_type == 'Offline'){
						if($settings->allow_offline_sessions == 0){
							$recordFailure($date, $time_from, $time_to, __('mentor.offline_not_allowed'));
							continue;
						}
					}

						if (Carbon::parse($date . ' ' . $time_from, $slotZone)->isPast()) {
							$recordFailure($date, $time_from, $time_to, __('mentor.slot_in_past'));
							continue;
						}

					$fileName = '';
					if ($request->hasFile('file_url')) {
						$file = $request->file('file_url');
						$fileName = app(\App\Services\FileManagerService::class)->upload($file, '', 'UsersDiskInviteMentor', 'common');
					}

					$autoApprove = $cycle->auto_approve_mentorship_requests;
					$approved = 'Yes';

					$userid = $request->user_id;
					$topic = "Online mentorship meeting (M{$request->mentorid}-U{$userid})";
					// GV-9566: the slot's date AND time -- the date alone booked Zoom at midnight.
					$data = ['topic' => $topic, "start_time" => $date . ' ' . $time_from, 'duration' => $period, 'host_video' => 1, 'participant_video' => 1];
					$onlineMeeting = $this->meetingService->createMeeting($data + ['timezone' => $slotZone ?? null]);

					if (!$onlineMeeting['success']) {
						$recordFailure($date, $time_from, $time_to, __('mentor.meeting_creation_failed'));
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
						'admin_id' => Auth::id(),
						'start_url' => $startUrl,
						'join_url' => $joinUrl,
						'zoom_id' => $meetingId,
						'zoom_passcode' => $passcode,
						'zoom_date' => $datefrom,
						'period' => $period,
						'organization_id' => $request->organization_id,
						'cycle_id' => $request->cycle_id,
						'fileurl' => $fileName,
						'session_type' => $slot_session_type
					]);

					// $this->notifyFounderOfReservedSession($createMentor);

					$successCount++;

					$start = Carbon::parse($datefrom);
					$end = $start->copy()->addMinutes($period);
					$sessionType = $slot_session_type;
					$location = $sessionType === 'Offline'
						? ($cycle->offline_location_address ?? optional($cycle->organization)->address ?? null)
						: null;

					$bookedSessions[] = [
						'founder_name' => '',
						'startup_name' => '',
						'date' => $start->format('Y-m-d'),
						'start_time' => $start->format('h:i A'),
						'end_time' => $end->format('h:i A'),
						'type' => $sessionType,
						'location' => $location,
						'link' => $sessionType === 'Online' ? $joinUrl : null,
						'is_hackathon' => strtolower($cycle->type) === 'hackathon'
					];

					$founder = User::find($userid);
					if ($founder) {
						$founder->notify(new MentorshipSessionFounderBookingNotification(
							$requestedMentor,
							$date,
							$time_from,
							$time_to
						));
					}

					if (!in_array(env('APP_ENV', 'local'), ['local'])) {
						SendMentorshipRequestJob::dispatch($request->mentorid, $date, $request->period, $userid, $onlineMeeting, $createMentor->id, $approved, 'addwait');
					}
				}

				/* $mentor = User::find($request->mentorid);
				$founder = User::find($request->user_id);
				if ($successCount > 0 && $mentor && $founder) {
					$mentor->notify(new MentorshipSessionMentorBookingNotification($founder, $bookedSessions));
				} */

				if ($wantsJson) {
					if ($successCount === 0) {
						$status  = 'error';
						$message = __('mentor.no_sessions_booked');
					} elseif (count($failedSlots) > 0) {
						$status  = 'partial';
						$message = __('mentor.some_sessions_booked', [
							'booked' => $successCount,
							'total'  => $requestedCount,
						]);
					} else {
						$status  = 'success';
						$message = __('mentor.session_booked_successfully');
					}

					return response()->json([
						'status'    => $status,
						'booked'    => $successCount,
						'requested' => $requestedCount,
						'failed'    => $failedSlots,
						'message'   => $message,
					], 200);
				}

				// Legacy plain-form fallback.
				if ($successCount > 0) {
					return back()->with(['message' => "Request_successfully_added_Startup"]);
				}
				return back()->with(['message2' => "Error, can't add request", 'errors' => $errors]);
			}
		}
		else
		{
			$startupName = $user?->company?->name ?: $user->name;
			$cycle = $user?->company?->cycledata;
			// Recipients: the organization manager(s) of the organization that
			// manages the program (cycle) the founder is currently enrolled in,
			// the sub-admin(s) that organization is assigned to, and the
			// platform's super admin(s). One role query covers both roles.
			// Role::_users() already returns a Collection; the trailing ->get() threw an
			// ArgumentCountError on every founder self-booking (since GV-7173).
			$rolesUsers = Role::_users(['organizationManager', 'admin']);
			$orgManagers = $rolesUsers->filter(fn ($u) => $u->hasRole('organizationManager') && $u->organization_id == $cycle->organization_id);
			$superAdmins = $rolesUsers->filter(fn ($u) => $u->hasRole('admin'));
			$subAdmins = $cycle->organization->assignedUsers;
			$parties = $orgManagers->merge($subAdmins)->merge($superAdmins)->unique('id');

			$this->meetingService = OnlineMeetingFactory::create();
			$datefrom = $request->date . ' ' . $request->session_start_time;
			$dateto = $request->date . ' ' . $request->session_end_time;
			if ($request->period == '') {
				$period = Carbon::parse($datefrom)->diffInMinutes(Carbon::parse($dateto));
			} else {
				$period = $request->period;
			}
			$dayName = Carbon::parse($request->date)->format('l');
			
			$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `date` LIKE ? AND tfrom <= ? AND tto >= ?";
			$schedules = DB::select($schedules_q, [
				$request->mentorid,
				'%' . $request->date . '%',
				$request->session_start_time,
				$request->session_end_time
			]);

			if ($schedules == null) {
				$schedules_q = "SELECT * FROM schedules WHERE user_id = ? AND `days` LIKE ? AND tfrom <= ? AND tto >= ?";
			$schedules = DB::select($schedules_q, [
				$request->mentorid,
				'%' . $dayName . '%',
				$request->session_start_time,
				$request->session_end_time
			]);
				// return back()->with(['class' => 'danger','msg' => 'mentor_not_available_in_this_time']);
			}
			if ($schedules == null) {
				return back()->with(['class' => 'danger','msg' => 'mentor_not_available_in_this_time']);
			}

			// A slot belongs to one startup. The modal hides taken slots, but
			// re-check here to cover a stale page or two founders submitting at
			// the same time. 'No' (rejected) does not hold the slot.
			$alreadyBooked = MentorshipRequest::where('mentor_id', $request->mentorid)
				->where('zoom_date', $datefrom)
				->where(function ($q) {
					$q->where('approved', 'Yes')->orWhereNull('approved');
				})
				->exists();

			if ($alreadyBooked) {
				return back()->with(['class' => 'danger', 'msg' => 'slot_just_booked']);
			}

			if($request->session_type == 'Offline'){
				
				if($settings->allow_offline_sessions == 0){
					
					return back()->with(['class' => 'danger','msg' => 'Offline sessions are not allowed']);
				}
			}
				
			// GV-9566: the slot is wall-clock in the program organization's platform zone.
			$slotZone = TimezoneService::platform($cycle->organization_id ? (int) $cycle->organization_id : null);
			if (\App\Settings::hasReachedMaxConcurrentSessions(Carbon::parse($datefrom, $slotZone), $period)) {
				return back()->with(['class' => 'danger', 'msg' => __('api.max_concurrent_sessions_reached')]);
			}

			if ($request->mentorid) {
				
				if (Carbon::parse($request->date . ' ' . $request->session_start_time, $slotZone)->isPast()) {
					return back()->with(['msg' => 'cannot_schedule_past_meeting']);
				}
				$fileName = '';
				// if ($request->hasFile('file_url')) {
				// 	$fileName = $request->file('file_url');
				// 	$fileName = $fileName->store(Auth::id(), ['disk' => 'PrivateDisk']);
				// }
				if ($request->hasFile('file_url')) {
					$file = $request->file('file_url');
					$fileName = app(\App\Services\FileManagerService::class)->upload($file, '', 'UsersDiskInviteMentor', 'common');
				}

				$autoApprove = $cycle->auto_approve_mentorship_requests;

				$approved = null;
				$userid = Auth::id();
				$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $userid . ')';
				// GV-9566: the slot's date AND time -- the date alone booked Zoom at midnight.
				$data = ['topic' => $topic, "start_time" => $request->date . ' ' . $request->session_start_time, 'duration' => $period, 'host_video' => 1, 'participant_video' => 1];
				$onlineMeeting = $this->meetingService->createMeeting($data + ['timezone' => $slotZone ?? null]);
				if (!$onlineMeeting['success']) {
					return back()->with(['msg' => "meeting_error", 'class' => 'danger']);
				}
				$meetingId = $onlineMeeting['data']['id'];
				$startUrl = $onlineMeeting['data']['start_url'];
				$joinUrl = $onlineMeeting['data']['join_url'];
				$passcode = $onlineMeeting['data']['passcode'] ?? null;
				$zoom_date = $datefrom;
				if( $autoApprove == 1 )
				{
					$approved = 'Yes';																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																	
					$dateFormat = new \DateTime($datefrom);
					$zoom_date = $dateFormat->format('Y-m-d H:i');
				}
				$createMentorshipRequest = MentorshipRequest::create([
					'mentor_id' => $request->mentorid,
					'user_id' => $userid,
					'comment' => $request->notes,
					'approved' => $approved,
					'start_url' => $startUrl,
					'join_url' => $joinUrl,
					'zoom_id' => $meetingId,
					'zoom_passcode' => $passcode,
					'zoom_date' => $zoom_date,
					'period' => $period,
					'fileurl' => $fileName,
					'organization_id' => $cycle->organization_id,
					'cycle_id' => $cycle->id,
					'session_type' => $request->session_type
				]);
				// $this->notifyFounderOfReservedSession($createMentorshipRequest);
				if (!in_array(env('APP_ENV', 'local'), ['local'])) {
					// SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), null, null, 'addwait');

					// Atomically claim the "notify org manager" step so a retry/duplicate
					// call for this same request can never send the email twice.
					$claimedNotify = MentorshipRequest::where('id', $createMentorshipRequest->id)
						->whereNull('manager_notified_at')
						->update(['manager_notified_at' => now()]);

					if ($claimedNotify) {
						$isOnlineRequest = strtolower($request->session_type ?? '') == 'online';
						$requestAttachments = $fileName ? [[
							'url'  => app(\App\Services\FileManagerService::class)->getUrl($fileName, 'UsersDiskInviteMentor'),
							'name' => basename($fileName),
						]] : [];

						// MentorshipSessionRequestNotification is ShouldQueue, so notify()
						// only writes a queue row here -- the old sleep(2) between chunks
						// throttled nothing and simply held the founder's request open for
						// two seconds per pair of recipients. Keep the same send pacing by
						// staggering delivery on the queue instead of blocking the response.
						$allowedPerSecond = 2;
						$secondsBetweenChunks = 2;

						foreach ($parties->chunk($allowedPerSecond) as $chunkIndex => $chunkParties) {
							$sendAt = now()->addSeconds($chunkIndex * $secondsBetweenChunks);

							foreach ($chunkParties as $party) {
								$party->notify(
									(new MentorshipSessionRequestNotification(
										$startupName,
										$requestedMentor,
										$dateTimePayload,
										$requestNotes,
										$user,
										$user->company,
										$request->session_type,
										$isOnlineRequest ? $joinUrl : null,
										$isOnlineRequest ? $passcode : null,
										!$isOnlineRequest ? ($cycle->offline_location_address ?? optional($cycle->organization)->address) : null,
										!$isOnlineRequest ? ($cycle->offline_location_map ?? optional($cycle->organization)->google_map_url) : null,
										!$isOnlineRequest ? (optional($cycle->organization)->address) : null,
										$requestAttachments,
										$cycle?->organization
									))->delay($sendAt)
								);
							}
						}
					}
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

			$bookedSessions = [];
			$cycle = Cycle::find($request->cycle_id);

			foreach ($dates as $key) {
				$date = ltrim($key);
				
				// GV-9566: typed slot is platform wall-clock.
				$slotZone = TimezoneService::platform($request->organization_id ? (int) $request->organization_id : null);
				if (\App\Settings::hasReachedMaxConcurrentSessions(Carbon::parse($date, $slotZone), $request->period)) {
					return back()->with(['message2' => __('api.max_concurrent_sessions_reached'), 'notAdd' => 2]);
				}

				$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $request->userid . ')';
				$data = ['topic' => $topic, "start_time" => $date, 'duration' => $request->period, 'host_video' => 1, 'participant_video' => 1];

				$onlineMeeting = $this->meetingService->createMeeting($data + ['timezone' => $slotZone ?? null]);

				if (!$onlineMeeting['success']) {
					return back()->with(['message2' => "meeting_error"]);
				}
				// $date = substr($date1, 0, -2);
				// $dateAM = substr($date1, -2);
				$dateFormat = new \DateTime($date);
				// dd($dateFormat->format('Y-m-d H:i'));
				$createMentor = MentorshipRequest::create([
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

				$this->notifyFounderOfReservedSession($createMentor);
				$start = Carbon::parse($date);
				$end = $start->copy()->addMinutes($request->period);
				$sessionType = 'Online';
				$joinUrl = $onlineMeeting['data']['join_url'];

				$bookedSessions[] = [
					'founder_name' => '',
					'startup_name' => '',
					'date' => $start->format('Y-m-d'),
					'start_time' => $start->format('h:i A'),
					'end_time' => $end->format('h:i A'),
					'type' => $sessionType,
					'location' => null,
					'link' => $joinUrl,
					'is_hackathon' => $cycle ? (strtolower($cycle->type) === 'hackathon') : false
				];

				SendMentorshipRequestJob::dispatch($request->mentorid, $request->date, $request->period, auth()->id(), $onlineMeeting, null, null, 'add');
				/// End of mail send
			}

			$mentor = User::find($request->mentorid);
			$founder = User::find($request->userid);
			if ($mentor && $founder && !empty($bookedSessions)) {
				$organization = Organization::find($request->organization_id) ?? $cycle?->organization;
				$mentor->notify(new MentorshipSessionMentorBookingNotification($founder, $bookedSessions, $organization));
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
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		if ($request->show == 'all') {
			$query = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->where(function ($q) use ($orgIds) {
					$q->whereHas('cycle', fn($c) => $c->whereIn('organization_id', $orgIds));
				});
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
			$query = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->where(function ($q) use ($orgIds) {
					$q->whereHas('cycle', fn($c) => $c->whereIn('organization_id', $orgIds));
				});
			})->whereNotNull('start_url')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				$startDate = Carbon::parse($request->start_date)->startOfDay();
				$endDate = Carbon::parse($request->end_date)->endOfDay();
				$q->whereBetween('zoom_date', [$startDate, $endDate]);
			})->where('approved', 'Yes')->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		} elseif ($request->show == 'disapproved') {
			$query = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->where(function ($q) use ($orgIds) {
					$q->whereHas('cycle', fn($c) => $c->whereIn('organization_id', $orgIds));
				});
			})->where('approved', 'No')->when($request->start_date && $request->end_date, function ($q) use ($request) {
				$startDate = Carbon::parse($request->start_date)->startOfDay();
				$endDate = Carbon::parse($request->end_date)->endOfDay();
				$q->whereBetween('zoom_date', [$startDate, $endDate]);
			})->when($request->filled('cycle_id') && $request->cycle_id !== 'all', function ($q) use ($request) {
				// التحقق من وجود cycle_id
				return $q->where('cycle_id', $request->cycle_id);
			});
		} else {
			$query = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->where(function ($q) use ($orgIds) {
					$q->whereHas('cycle', fn($c) => $c->whereIn('organization_id', $orgIds));
				});
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
		if ($request->filled('session_type') && $request->session_type != 'all') {
			$query->where('session_type', $request->session_type);
		}

		$sessions = $query->with(['user.company', 'mentor'])->orderBy('id', 'DESC')->get();

		// Add rate filtering - filter after retrieval for better accuracy
		if ($request->filled('rate') && $request->rate != 'all' && ($request->show == 'all' || $request->show == 'approved')) {
			$rating = $request->rate;
			$sessions = $sessions->filter(function ($session) use ($rating) {
				// Filter sessions by their individual rating
				$sessionRating = $session->rating ?? 0;
				return round($sessionRating) == $rating;
			});
		}

		$sessions = $sessions->map(function ($session) {
			$session->date = Carbon::parse($session->zoom_date);
			return $session;
		});

		if(!empty($orgIds) && !$user->hasRole('admin')) {
			$cycles = \App\Cycle::whereIn('organization_id', $orgIds)->get();
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
			
			// GV-9566: the edited time is platform wall-clock of the session's organization.
			$slotZone = TimezoneService::platform(MentorshipRequest::whereKey($request->id)->value('organization_id'));
			if (\App\Settings::hasReachedMaxConcurrentSessions(Carbon::parse($request->date, $slotZone), $request->period)) {
				return back()->with(['message2' => __('api.max_concurrent_sessions_reached')]);
			}

			$approved = $start = $join = null;
			if ($request->approved) {
				$approved = 'Yes';
				$topic = "Online mentorship meeting (M" . $request->mentorid . '- U' . $request->userid . ')';
				$data = ['topic' => $topic, "start_time" => $request->date, 'duration' => $request->period, 'host_video' => 1, 'participant_video' => 1];
				
				$onlineMeeting = $this->meetingService->createMeeting($data + ['timezone' => $slotZone ?? null]);
				
				if (!$onlineMeeting['success']) {
					return back()->with(['message2' => "Error, can't create Online meeting"]);
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
				$mRequest = MentorshipRequest::findOrFail($request->id);
				if ($request->stat == 'Yes') {
					$topic = "Online mentorship meeting (M" . $mRequest->mentor_id . '- U' . $mRequest->user_id . ')';
					$data = ['topic' => $topic, "start_time" => $mRequest->zoom_date, 'duration' => $mRequest->period, 'host_video' => 1, 'participant_video' => 1];
				}
				 MentorshipRequest::find($request->id)->update(['approved' => $request->stat]);
				$locale = app()->getLocale() ?? 'en';
				/// Notify Mentor
				// dd($request->all(), $mRequest->mentor_id, $mRequest->zoom_date->format('Y-m-d H:i:s'), $mRequest->period, auth()->id(), null, $request->id, $request->stat, 'modify');
				SendMentorshipRequestJob::dispatch($mRequest->mentor_id, $mRequest->zoom_date->format('Y-m-d H:i:s'), $mRequest->period, auth()->id(), null, $request->id, $request->stat, 'modify');


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
						->where('zoom_date', '>=', TimezoneService::platformToday() . ' 00:00:00')
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

				$now = now();
				$rows = [];
				foreach ($periods as $day => $times) {
					// التحقق من وجود بيانات "from" و "to"
					if (isset($times['from']) && isset($times['to'])) {
						foreach ($times['from'] as $index => $from) {
							$to = $times['to'][$index] ?? null; // تأكد من وجود القيمة المقابلة في "to"

							// تجاهل إذا كانت القيم فارغة
							if (!is_null($from) && !is_null($to)) {
								$rows[] = [
									'user_id' => $userId,
									'type' => $request->input('type'),
									'days' => $day,
									'tfrom' => $from,
									'tto' => $to,
									'notes' => $request->input('notes'),
									'created_at' => $now,
									'updated_at' => $now,
								];
							}
						}
					}
				}
				if (!empty($rows)) {
					Schedule::insert($rows);
				}
				return back()->with(['msg' => 'Saved', 'class' => 'success']);
			}
		} catch (Exception $e) {
			return response()->json(['state' => 'failed', 'e' => $e], 500);
		}
	}

	public function mine()
	{
		$sessions = MentorshipRequest::with(['user', 'mentor', 'organization', 'cycle'])->where('user_id', Auth::id())->orderby('created_at', 'DESC')->get();
		$sessions = $sessions->map(function ( $session ) {
			$session->address = $session->cycle && $session->cycle->offline_location_address ? $session->cycle->offline_location_address : optional($session->organization)->address;
			$session->google_map_url = $session->cycle && $session->cycle->offline_location_map ? $session->cycle->offline_location_map : optional($session->organization)->google_map_url;
			$session->meeting_status = mentorshipMeetingStatus($session->zoom_date, $session->period);
			return $session;
		});
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

	/**
	 * Fetch session reports and recordings data for modal (AJAX endpoint)
	 * Similar to TrainingController@getSessionReportsData but for mentorship sessions
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

			// For mentorship, we use MentorshipRequest model
			$session = MentorshipRequest::with(['reports', 'recordings'])
				->find($sessionId);

			if (!$session) {
				return response()->json([
					'success' => false,
					'message' => __('admin.session_not_found')
				], 404);
			}

			// Same ownership rule as the API endpoint: the session must belong to the
			// caller's organizations, or the caller must be the session's own mentor.
			$authUser = auth()->user();
			if (!$authUser->hasRole('admin')
				&& !$authUser->ownsOrganization($session->organization_id ?? $session->cycle?->organization_id)
				&& (int) $session->mentor_id !== (int) $authUser->id) {
				return response()->json([
					'success' => false,
					'message' => __('admin.session_not_found')
				], 404);
			}

			// Store original counts for debugging
			$originalReportsCount = $session->reports->count();
			$originalRecordingsCount = $session->recordings->count();

			// Auto-fetch reports if none exist and session has zoom_id
			if ($session->reports->count() === 0 && $session->zoom_id) {
				try {
					$this->meetingService = OnlineMeetingFactory::create();
					if ($this->meetingService && method_exists($this->meetingService, 'getSessionReport')) {
						$response = $this->meetingService->getSessionReport($session->zoom_id);
						
						$serviceClass = get_class($this->meetingService);
						$isZoom = stripos($serviceClass, 'zoom') !== false;
						$isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;
						
						// Process Zoom reports
						if ($isZoom && !empty($response['success']) && !empty($response['data']['participants'])) {
							foreach ($response['data']['participants'] as $participant) {
								$joinDate = isset($participant['join_time']) ? Carbon::parse($participant['join_time'])->utc()->toDateTimeString() : null;
								$endDate = isset($participant['leave_time']) ? Carbon::parse($participant['leave_time'])->utc()->toDateTimeString() : null;

								\App\SessionReport::updateOrCreate(
									[
										'mentorship_request_id' => $session->id,
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

								\App\SessionReport::updateOrCreate(
									[
										'mentorship_request_id' => $session->id,
										'participant_user_id' => $participantUserId,
									],
									[
										'participant_email' => $email,
										'participant_name' => $participantName,
										'participant_join_date' => $joinDateParsed,
										'participant_end_date' => $leaveDateParsed,
										'attendance_duration' => $duration ?? 0,
									]
								);
							}
							// Refresh the session data to include new reports
							$session->load('reports');
						}
					}
				} catch (\Throwable $e) {
					// Silent fail for auto-fetch
				}
			}

			// Auto-fetch recordings if none exist and session has zoom_id
			if ($session->recordings->count() === 0 && $session->zoom_id) {
				try {
					$this->meetingService = OnlineMeetingFactory::create();
					if ($this->meetingService && method_exists($this->meetingService, 'getSessionRecord')) {
						$response = $this->meetingService->getSessionRecord($session->zoom_id);
						
						$serviceClass = get_class($this->meetingService);
						$isZoom = stripos($serviceClass, 'zoom') !== false;
						$isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;
						
						// Process Zoom recordings
						if ($isZoom && !empty($response['data']['recording_files'])) {
							foreach ($response['data']['recording_files'] as $file) {
								$recordingTime = isset($file['recording_start']) ? Carbon::parse($file['recording_start'])->utc()->toDateTimeString() : null;
								$password = $response['data']['password'] ?? null;

								\App\SessionRecording::updateOrCreate(
									['recording_id' => $file['id']],
									[
										'mentorship_request_id' => $session->id,
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
						}
						// Process Teams recordings
						elseif ($isTeams && !empty($response['data'][0])) {
							$file = $response['data'][0];
							$recordingId = $file['id'] ?? null;
							$recordingUrl = $file['recordingContentUrl'] ?? $file['meetingId'] ?? null;
							$createdDateTime = isset($file['createdDateTime']) ? Carbon::parse($file['createdDateTime'])->utc()->toDateTimeString() : null;

							if ($recordingId) {
								\App\SessionRecording::updateOrCreate(
									['recording_id' => $recordingId],
									[
										'mentorship_request_id' => $session->id,
										'recording_type' => 'Teams Recording',
										'recording_view_url' => $recordingUrl,
										'recording_download_url' => $recordingUrl,
										'recording_time' => $createdDateTime,
										'passcode' => null,
									]
								);
								// Refresh the session data to include new recordings
								$session->load('recordings');
							}
						}
					}
				} catch (\Throwable $e) {
					// Silent fail for auto-fetch
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

			// Prepare reports data
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

			// Calculate summary data
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
						'title' => $session->comment ?? 'Mentorship Session',
						'type' => 'mentorship',
						'datetime' => $session->zoom_date,
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
					'reports_count_before' => $originalReportsCount,
					'recordings_count_before' => $originalRecordingsCount,
					'reports_count_after' => $session->reports->count(),
					'recordings_count_after' => $session->recordings->count(),
				]
			]);

		} catch (\Throwable $e) {
			Log::error('Error in MentorsController@getSessionReportsData: ' . $e->getMessage());
			
			return response()->json([
				'success' => false,
				'message' => __('admin.error_fetching_session_data')
			], 500);
		}
	}

	public function availability()
	{
			return view('mentor.availability.index');
	}

	public function mentorshipSessions() 
	{
		$user = auth()->user();

		$mentorshipRequests = MentorshipRequest::when($user->hasRole('mentor'), function($query) use ($user) {
			$query->where('mentor_id', $user->id);
		})->with(['mentor', 'user', 'user.company', 'cycle', 'orgnization'])->orderBy('created_at', 'DESC')->get();
		$surveys = SurveyResponses::where('user_id', $user->id)->where('triggered_on', 'mentorship')->get();
			
		// GV-9566: the view compares $now with start_time/end_time as strings, so all
		// three are read in ONE zone (global platform); zoom_date is the cast instant.
		$platformZone = TimezoneService::platform();
		$now = TimezoneService::platformNow($platformZone)->format('Y-m-d H:i');
		$mentorship_sessions = $mentorshipRequests->map(function ($session) use ( $surveys, $platformZone, $user ) {
			$survey = $surveys->get($session->id);
			$_sesssion = new stdClass;
			$_sesssion->id = $session->id;
			// Display only: shown in the mentor's own timezone.
			$_sesssion->zoom_date = TimezoneService::forViewer($session->zoom_date, $user);
			$_sesssion->period = $session->period;
			if ($session->zoom_date) {
				$meetingStatus = mentorshipMeetingStatus($session->zoom_date, $session->period);
				$_sesssion->status = $meetingStatus === 'before' ? 'Upcoming' : ($meetingStatus === 'expired' ? 'Completed' : 'In-progress');
				$_sesssion->start_time = $session->zoom_date->copy()->subMinutes(30)->setTimezone($platformZone)->format('Y-m-d H:i');
				$_sesssion->end_time   = $session->zoom_date->copy()->addMinutes((int) $session->period)->setTimezone($platformZone)->format('Y-m-d H:i');
			} else {
				$_sesssion->status     = 'Upcoming';
				$_sesssion->start_time = null;
				$_sesssion->end_time   = null;
			}
			$_sesssion->session_type = $session->session_type;
			$_sesssion->approved = $session->approved;
			$_sesssion->start_url = $session->start_url;
			$_sesssion->comment = $session->comment;
			$_sesssion->location_address = $session->cycle->offline_location_address
				?? optional($session->orgnization)->address
				?? null;
			$_sesssion->google_map_url = $session->cycle->offline_location_map
				?? optional($session->orgnization)->google_map_url
				?? null;

			$_sesssion->mentor = new stdClass;
			$_sesssion->mentor->id = $session->mentor->id;
			$_sesssion->mentor->name = trim( $session->mentor->first_name_en . ' ' . $session->mentor->last_name_en );
			$_sesssion->mentor->image_url = User::image_handler($session->mentor->img_url);

			$_sesssion->user = new stdClass;
			$_sesssion->user->id = $session->user->id;
			$_sesssion->user->name = trim( $session->user->first_name_en . ' ' . $session->user->last_name_en );
			$_sesssion->user->image_url = User::image_handler($session->user->img_url);
			$_sesssion->user->company = new stdClass;
			$_sesssion->user->company->id = optional($session->user->company)->id;
			$_sesssion->user->company->name = optional($session->user->company)->name_en;
			$_sesssion->user->company->logo_url = optional($session->user->company)->logo ? asset($session->user->company->logo) : '/images/logo-placeholder.png';
			$_sesssion->survey = null;
			if( isset($survey->id) )
			{
				$_sesssion->survey = new stdClass;
				$_sesssion->survey->id = optional($survey)->id;
				$_sesssion->survey->status = $survey->responses != null ? 'Completed' : 'Pending';
				$_sesssion->survey->is_required = optional($survey)->survey->is_required ?? false;
			}

			return $_sesssion;
		});

		$mentorship_sessions = $mentorship_sessions->sortBy(function($session) {
			$statusPriority = [
				'In_Progress' => 1,
				'Pending' => 2,
				'Completed' => 3,
			];
			return $statusPriority[$session->status] ?? 999;
		});

		return view('mentor.mentorship-sessions.index', compact('mentorship_sessions', 'now'));
	}	
	
	public function surveys() 
	{
		$user = auth()->user();

		

		$availableSurveys = SurveyResponses::where('user_id', $user->id)
			->where('triggered_on', 'mentorship')
			->with('survey_scope')
			->orderBy('created_at', 'desc')
			->get();
			

				$surveyQuestionsCount = SurveyQuestions::whereIn(
				'survey_id',
				$availableSurveys->pluck('survey_id')
			)
			->select('survey_id', DB::raw('count(*) as total'))
			->groupBy('survey_id')
			->pluck('total', 'survey_id');

			$mentorshipRequests = MentorshipRequest::where('mentor_id', $user->id)
			->with('user.company')
			->orderBy('zoom_date', 'desc')
			->get();

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
				 
			return (object) [
				'id' => $survey->id,
				'title' => $survey->survey->title . ' Survey',
				'created_at' => $survey->created_at->format('Y-m-d H:i'),
				'due_date' => $survey->created_at->copy()->addDays(7)->format('Y-m-d H:i'),
				'status' => $completedQuestions === 0
					? 'Pending'
					: ($completedQuestions < $totalQuestions ? 'Pending' : 'Completed'),
				'survey_type' => ucfirst($survey->triggered_on),
				'total_questions' => $totalQuestions,
				'completed_questions' => $completedQuestions,
				'is_required' => (bool) $survey->survey->is_required,
				'user' => $mentorship
					? (object)[
						'id' => $mentorship->user->id,
						'name' => trim(
							$mentorship->user->first_name_en . ' ' .
							$mentorship->user->last_name_en
						),
						'image_url' => $mentorship->user->img_url
							? asset($mentorship->user->img_url)
							: '/icons/avatar.svg',

						'company' => (object)[
							'id' => optional($mentorship->user->company)->id,
							'name' => optional($mentorship->user->company)->name_en,
							'logo_url' => optional($mentorship->user->company)->logo
								? asset($mentorship->user->company->logo)
								: '/images/logo-placeholder.png',
						],
					]
					: null,
				'session' => $mentorship
					? (object)[
						'id' => $mentorship->id,
						'zoom_date' => TimezoneService::forViewer($mentorship->zoom_date, $user)->format('Y-m-d H:i'),
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

		return view('mentor.surveys.index', compact('surveys'));
	}
	function surveyStatus($survey, $totalQuestions) {
		$answered = count(json_decode($survey->responses, true) ?? []);

		if ($answered === 0) return 'Pending';
		if ($answered < $totalQuestions) return 'In_Progress';
		return 'Completed';
	}

	public function joinSession($id)
	{
		$session = MentorshipRequest::findOrFail($id);
		$user = Auth::user();
		if($user->hasRole('mentor')){
			$getDataFromMentor = $user->mentorSessions->where('id', $session->id)->first()->user->company;
		}elseif($user->hasRole('registered')){
			$getDataFromMentor = $user->company;
		}else{
			$getDataFromMentor = null;
		}
		$companyId = $getDataFromMentor ? $getDataFromMentor->id : $user->company_id;
		$companyCycle = $getDataFromMentor ? $getDataFromMentor->cycle : $user->company->cycle;
		$companyStep = $getDataFromMentor ? $getDataFromMentor->step : $user->company->step;
		$data = [
			'mentorship_request_id' => $session->id,
			'user_id'               => $user->id,
			'attendance'            => true,
			'location'              => 'online',
			'company_id'            => $companyId,
			'cycle_id'              => $companyCycle,
			'step_id'               => $companyStep
			];

		AttendanceTrainingOffline::updateOrCreate(
			$data,
			[
				'attendance' => true,
				'location'   => 'online',
			]
		);
		if($user->hasRole('mentor')){
			return redirect()->away($session->start_url);
		}else{
			return redirect()->away($session->join_url);
		}
	}

	private function notifyFounderOfReservedSession($mentorshipRequest)
	{
		try {
			$startupUser = $mentorshipRequest->user;
			if ($startupUser && $startupUser->company) {
				$founder = $startupUser->company->resolveUser();
				if ($founder && $founder->id !== Auth::id()) {
					// GV-9566: shown in the founder's own timezone.
					$zoomDate = TimezoneService::forViewer($mentorshipRequest->zoom_date, $founder);
					$duration = $mentorshipRequest->period ?? 60;
					$endDate = $zoomDate->copy()->addMinutes($duration);

					$sessionData = [
						'mentor_name' => $mentorshipRequest->mentor->name ?? 'N/A',
						'session_date' => $zoomDate->format('Y-m-d'),
						'start_time' => $zoomDate->format('H:i:s'),
						'end_time' => $endDate->format('H:i:s'),
						'session_type' => $mentorshipRequest->session_type ?? 'Online',
						'location' => $mentorshipRequest->orgnization->address ?? ($mentorshipRequest->orgnization->google_map_url ?? null),
						'meeting_link' => $mentorshipRequest->join_url ?? null,
					];

					$founder->notify(new MentorshipSessionFounderNotification($sessionData, $mentorshipRequest->organization ?? $mentorshipRequest->cycle?->organization));
				}
			}
		} catch (\Exception $e) {
			\Log::error('Failed to notify founder of reserved session: ' . $e->getMessage());
		}
	}
}
