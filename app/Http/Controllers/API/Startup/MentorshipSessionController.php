<?php

namespace App\Http\Controllers\API\Startup;

use App\Schedule;
use Carbon\Carbon;
use App\MentorshipRequest;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use App\Http\Controllers\Controller;
use App\Jobs\SendMentorshipRequestJob;
use App\Factories\OnlineMeetingFactory;
use App\Http\Requests\StoreMentorshipSessionRequest;

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

        if(!$startup->hasRole('Registered')){
           return $this->forbidden(__('api.forbidden'));
        }

        $mentorId = $request->mentor_id;
        $userId = $startup->id;
        $date = Carbon::parse($request->preferred_date_time);
        $duration = $request->duration_minutes;

        $conflict = MentorshipRequest::where('user_id', $userId)
            ->where('zoom_date', $date)
            ->whereNull('deleted_at')
            ->first();

        if ($conflict) {
             return $this->conflict(__('api.session_already_exists'));
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

        $onlineMeeting = $this->meetingService->createMeeting($data);
        $meetingId = $onlineMeeting['data']['id'] ?? null;
        $startUrl = $onlineMeeting['data']['start_url'] ?? null;
        $joinUrl = $onlineMeeting['data']['join_url'] ?? null;

        // Save MentorshipRequest
        $mentorship = new MentorshipRequest();
        $mentorship->mentor_id = $mentorId;
        $mentorship->user_id = $userId;
        $mentorship->zoom_date = $date;
        $mentorship->period = $duration;
        $mentorship->comment = $request->notes;
        $mentorship->organization_id = $request->organization_id;
        $mentorship->cycle_id = $request->cycle_id;
        $mentorship->override_reason = $request->override_reason ? $request->override_reason : null;
        $mentorship->start_url = $startUrl;
        $mentorship->join_url = $joinUrl;
        $mentorship->zoom_id = $meetingId;
        $mentorship->save();

        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
            SendMentorshipRequestJob::dispatch($mentorId,$date,$duration,$userId,null,null,'addwait');
        }

          return $this->created(
            ['session_id' => $mentorship->id,'status' => $mentorship->approved ? 'approved' : 'pending'],
             __('api.mentorship_session_created'),
             [],
            201);
    }
}
