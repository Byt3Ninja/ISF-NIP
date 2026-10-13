<?php

namespace App\Http\Controllers\API\Startup;

use App\Cycle;
use App\Schedule;
use Carbon\Carbon;
use App\MentorshipRequest;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\TimezoneService;
use App\Services\WhatsAppService;
use App\Http\Controllers\Controller;
use App\Jobs\SendMentorshipRequestJob;
use App\Factories\OnlineMeetingFactory;
use App\Http\Resources\MentorshipSessionResource;
use App\Http\Requests\SubmitSessionFeedbackRequest;
use App\Http\Requests\StoreMentorshipSessionRequest;
/**
 * @OA\Schema(
 *     schema="MentorshipSessionResource",
 *     type="object",
 *     title="Completed Mentorship Session",
 *     @OA\Property(property="sessionId", type="integer", example=101),
 *     @OA\Property(property="mentorId", type="integer", example=34),
 *     @OA\Property(property="mentorName", type="string", example="Ali Al-Mansouri"),
 *     @OA\Property(property="sessionDateTime", type="string", format="date-time", example="2025-07-25 14:00:00")
 * )
 */
class MentorshipSessionController extends Controller
{
    use ApiResponder;
    
    protected $meetingService;
	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->meetingService = OnlineMeetingFactory::create();
		$this->whatsAppService = $whatsAppService;
	}
    /**
 * @OA\Post(
 *     path="/api/mentorship-sessions",
 *     tags={"Mentorship Sessions"},
 *     summary="Request a mentorship session",
 *     description="Submit a new mentorship session request with a mentor. Only users with the 'Registered' role can access this endpoint.",
 *     operationId="addMentorshipSession",
 *     security={{"bearerAuth":{}}},
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "mentor_id", 
 *                 "preferred_date_time", 
 *                 "duration_minutes", 
 *                 "notes", 
 *                 "organization_id", 
 *                 "cycle_id"
 *             },
 *             @OA\Property(property="mentor_id", type="integer", example=128, description="ID of the mentor"),
 *             @OA\Property(property="preferred_date_time", type="string", format="date-time", example="2025-07-30T10:00:00", description="Preferred start time (ISO 8601 format)"),
 *             @OA\Property(property="duration_minutes", type="integer", example=45, description="Duration of the session in minutes"),
 *             @OA\Property(property="override_availability", type="boolean", example=false, description="Override mentor's availability"),
 *             @OA\Property(property="override_reason", type="string", nullable=true, example="Urgent meeting required", description="Reason for override (required if override_availability is true)"),
 *             @OA\Property(property="notes", type="string", example="أرغب في مناقشة استراتيجية التمويل.", description="Additional notes or discussion topic"),
 *             @OA\Property(property="organization_id", type="integer", example=7, description="User's organization ID"),
 *             @OA\Property(property="cycle_id", type="integer", example=43, description="Associated cycle ID")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=201,
 *         description="Mentorship session successfully created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Mentorship Session Request Submitted Successfully."),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="session_id", type="integer", example=1001),
 *                 @OA\Property(property="status", type="string", example="pending", enum={"approved", "pending"})
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden (not a startup)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=409,
 *         description="Conflict (session already exists)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="A session already exists at the selected time.")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=422,
 *         description="Validation error (invalid time or input)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="The selected time does not match the mentor’s availability.")
 *         )
 *     )
 * )
 */

    public function addMentorshipSession(StoreMentorshipSessionRequest $request)
    {
        $startup = auth()->user();
         
        if (!$startup->ableTo('mentors.settings_modify')) {
          return $this->forbidden(__('api.forbidden'));
      }

        // Derive the tenant from the caller, never from the request body: the
        // validation rules only assert the ids EXIST, so trusting them lets a
        // startup plant sessions into another tenant's organization/cycle.
        // Checked here, before any online meeting is created for the request.
        $callerOrganizationId = $startup->organizations_ids[0] ?? null;
        if ($request->organization_id !== null
            && !$startup->ownsOrganization($request->organization_id)) {
            return $this->forbidden(__('api.forbidden'));
        }

        if ($request->cycle_id !== null) {
            $cycle = Cycle::find($request->cycle_id);
            if (!$cycle || !$startup->ownsOrganization($cycle->organization_id)) {
                return $this->forbidden(__('api.forbidden'));
            }

            // mentor_id is only validated as an existing user: it must be a mentor of this program.
            if (!$cycle->mentors()->whereKey($request->mentor_id)->exists()) {
                return $this->forbidden(__('api.forbidden'));
            }
        }

        $mentorId = $request->mentor_id;
        $userId = $startup->id;
        // GV-9566: read in the organization's platform zone (an explicit client offset
        // wins) and kept there, so format('H:i:s') below compares with the mentor's
        // platform wall-clock schedules and the zoom_date binding is platform wall-clock.
        $zone = TimezoneService::platform($request->organization_id ? (int) $request->organization_id : null);
        $date = Carbon::parse($request->preferred_date_time, $zone)->setTimezone($zone);
        $duration = $request->duration_minutes;

        $conflict = MentorshipRequest::where('user_id', $userId)
            ->where('zoom_date', $date)
            ->whereNull('deleted_at')
            ->first();

        if ($conflict) {
             return $this->conflict(__('api.session_already_exists'));
        }

        if (\App\Settings::hasReachedMaxConcurrentSessions($date, $duration)) {
             return $this->validationError(__('api.max_concurrent_sessions_reached'));
        }

        if (!$request->override_availability) {
           $dayName = $date->format('l'); 
           $time = $date->format('H:i:s');

            $mentorSchedules = Schedule::where('user_id', $mentorId)
                ->where('active', 1)
                ->get();

            $validSlot = $mentorSchedules->filter(function ($slot) use ($dayName, $time, $duration) {
                $slotDays = explode(',', $slot->days);
                return in_array($dayName, $slotDays)
                    && $slot->tfrom <= $time
                    && Carbon::parse($time)->addMinutes($duration)->format('H:i:s') <= $slot->tto;
            })->count();

            if (!$validSlot) {
              return $this->validationError(__('api.invalid_time_not_in_schedule'));
            }
        }

        $topic = "Online Mentorship Meeting (M{$mentorId}-U{$userId})";
        $data = [
            'topic' => $topic,
            'start_time' => $date->toIso8601String(),
            'duration' => $duration,
            'host_video' => 1,
            'participant_video' => 1
        ];

        $onlineMeeting = $this->meetingService->createMeeting($data + ['timezone' => $zone]);
        $meetingId = $onlineMeeting['data']['id'] ?? null;
        $startUrl = $onlineMeeting['data']['start_url'] ?? null;
        $joinUrl = $onlineMeeting['data']['join_url'] ?? null;
        $passcode = $onlineMeeting['data']['passcode'] ?? null;

        // Save MentorshipRequest
        $mentorship = new MentorshipRequest();
        $mentorship->mentor_id = $mentorId;
        $mentorship->user_id = $userId;
        // organization_id first: the zoom_date cast resolves the zone from it.
        $mentorship->organization_id = $request->organization_id ?? $callerOrganizationId;
        $mentorship->zoom_date = $date;
        $mentorship->period = $duration;
        $mentorship->comment = $request->notes;
        $mentorship->cycle_id = $request->cycle_id;
        $mentorship->override_reason = $request->override_reason ? $request->override_reason : null;
        $mentorship->start_url = $startUrl;
        $mentorship->join_url = $joinUrl;
        $mentorship->zoom_id = $meetingId;
        $mentorship->zoom_passcode = $passcode;
        $mentorship->save();

        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
            SendMentorshipRequestJob::dispatch($mentorId, $date, $duration, $userId, null, $mentorship->id, null, 'addwait');
        }

          return $this->created(
            ['session_id' => $mentorship->id,'status' => $mentorship->approved ? 'approved' : 'pending'],
             __('api.mentorship_session_created'),
             [],
            201);
    }

    /**
 * @OA\Post(
 *     path="/api/mentorship-sessions/submit-feedback",
 *     tags={"Mentorship Sessions"},
 *     summary="Submit session Rate and feedback",
 *     description="Submit whether the user attended the session and optionally rate it if attended.",
 *     operationId="submitMentorshipSessionFeedback",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"session_id", "attended"},
 *             @OA\Property(property="session_id", type="integer", example=1001, description="ID of the mentorship session"),
 *             @OA\Property(property="attended", type="boolean", example=true, description="Whether the user attended the session"),
 *             @OA\Property(property="rating", type="integer", nullable=true, minimum=1, maximum=5, example=4, description="Rating given to the mentor (1-5), required if attended is true"),
 *             @OA\Property(property="comment", type="string", nullable=true, example="The session was very helpful", description="Optional comment about the session")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Feedback submitted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Feedback submitted successfully")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=409,
 *         description="Conflict (feedback already submitted)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Feedback already submitted for this session.")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="The given data was invalid.")
 *         )
 *     )
 * )
 */
     public function submitFeedback(SubmitSessionFeedbackRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
         if (!$user->ableTo('mentorships.sessions_rate')) {
         return $this->forbidden(__('api.forbidden'));
      }

        $session = MentorshipRequest::where('id', $data['session_id'])
            ->where('user_id',$user->id)
            ->firstOrFail();

        if (!is_null($session->done)) {
            return $this->conflict(__('api.session_feedback_already_submitted'));
        }

        $session->done = $data['attended'] ? 1 : 0;

        if ($data['attended']) {
            $session->rating = $data['rating'];
            $session->rate_comment = $data['comment'] ?? null;
        }

        $session->save();

        return $this->success(null, [], __('api.session_feedback_submitted'));
    }

    /**
 * @OA\Get(
 *     path="/api/mentorship-sessions/completed",
 *     tags={"Mentorship Sessions"},
 *     summary="View completed mentorship sessions",
 *     description="Returns a paginated list of past mentorship sessions the startup has attended.",
 *     operationId="getMyCompletedMentorshipSessions",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of completed sessions",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/MentorshipSessionResource")
 *             ),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="currentPage", type="integer", example=1),
 *                 @OA\Property(property="lastPage", type="integer", example=5),
 *                 @OA\Property(property="total", type="integer", example=48)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     )
 * )
 */
    public function myCompletedSessions(Request $request)
    {
        $user = auth()->user();
        if (!$user->ableTo('mentorships.list_mine')) {
         return $this->forbidden(__('api.forbidden'));
      }

       $sessions = MentorshipRequest::with([
        'mentor', 
        'mentor.bio', 
        'mentor.mentorExpertises',
    ])
    ->where('user_id', $user->id)
    ->where('approved', 'Yes')
    ->whereNull('rating')
    // GV-9566: zoom_date is platform wall-clock, so compare with the platform clock.
    ->where('zoom_date', '<', TimezoneService::platformNow()->format('Y-m-d H:i:s'))
    ->orderByDesc('zoom_date')
    ->paginate(10);

       return $this->success(
         MentorshipSessionResource::collection($sessions),
        [
            'meta' => [
                'currentPage' => $sessions->currentPage(),
                'lastPage' => $sessions->lastPage(),
                'total' => $sessions->total(),
                'links' => [
                    'first' => $sessions->url(1),
                    'last' => $sessions->url($sessions->lastPage()),
                    'prev' => $sessions->previousPageUrl(),
                    'next' => $sessions->nextPageUrl(),
                ],
            ]
        ],
     );
    }
}
