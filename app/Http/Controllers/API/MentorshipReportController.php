<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\SessionReport;
use App\SessionRecording;
use App\MentorshipRequest;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Factories\OnlineMeetingFactory;
use App\Services\MentorshipReportService;
use App\Http\Requests\DashboardFilterRequest;

class MentorshipReportController extends Controller
{
    use ApiResponder;
    protected $service;
    protected $meetingService;


    public function __construct(MentorshipReportService $service)
    {
        $this->service = $service;
    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/summary",
     *     summary="Get mentorship summary KPIs",
     *     description="Returns high-level statistics about mentorship.",
     *     operationId="getMentorshipSummary",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="program", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Summary data retrieved successfully",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Summary data retrieved successfully",
     *             "data": {
     *                 "totalMentors": 25,
     *                 "activeMentors": 18,
     *                 "requests": 120,
     *                 "approvedSessions": 100,
     *                 "completedSessions": 90,
     *                 "totalHours": 150.5,
     *                 "completionRate": 90.0,
     *                 "avgDuration": 60.0,
     *                 "avgAttendance": 90.0
     *             }
     *         })
     *     )
     * )
     */

    public function summary(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.summary')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getSummary($request->validated());
        return $this->success($data);
    }

   /**
     * @OA\Get(
     *     path="/api/dashboard/weekly-trends",
     *     summary="Get weekly mentorship trends",
     *     operationId="getWeeklyTrends",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="weeks", in="query", @OA\Schema(type="integer", example=8)),
     *     @OA\Parameter(name="program", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Weekly trends retrieved",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Weekly trends retrieved successfully",
     *             "data": {
     *                 "labels": {"W30", "W31", "W32", "W33"},
     *                 "requests": {10, 15, 12, 18},
     *                 "approvals": {8, 14, 11, 17}
     *             }
     *         })
     *     )
     * )
     */

    public function weeklyTrends(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.weekly_trends')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getWeeklyTrends($request->validated());
        return $this->success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/mentor-hours",
     *     summary="Get mentor hours",
     *     operationId="getMentorHours",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="program", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Mentor hours retrieved",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Mentor hours retrieved successfully",
     *             "data": {
     *                 {"id": 1, "name": "John Doe", "hours": 15.5},
     *                 {"id": 2, "name": "Jane Smith", "hours": 12.0}
     *             }
     *         })
     *     )
     * )
     */

    public function mentorHours(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.mentor_hours')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getMentorHours($request->validated());
        return $this->success($data);
    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/mentor-performance",
     *     summary="Get mentor performance statistics",
     *     operationId="getMentorPerformance",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Mentor performance data",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Mentor performance retrieved successfully",
     *             "data": {
     *                 {
     *                     "id": 1,
     *                     "name": "John Doe",
     *                     "programs": "Startup Bootcamp, Growth Launch",
     *                     "requests": 20,
     *                     "approved": 18,
     *                     "completed": 17,
     *                     "hours": 25.0,
     *                     "attendance": 94.4,
     *                     "rating": 4.7,
     *                     "completion_rate": 94.4
     *                 }
     *             }
     *         })
     *     )
     * )
     */

    public function mentorPerformance(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.mentor_performance')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getMentorPerformance($request->validated());
        return $this->success($data);
    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/startup-engagement",
     *     summary="Get startup engagement",
     *     operationId="getStartupEngagement",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Startup engagement data",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Startup engagement retrieved successfully",
     *             "data": {
     *                 {
     *                     "id": 5,
     *                     "name": "Ahmed Youssef",
     *                     "company": "SmartTech",
     *                     "programs": "Startup Sprint",
     *                     "requests": 10,
     *                     "completed": 9,
     *                     "hours": 9.5,
     *                     "attendance": 90.0,
     *                     "rating": 4.5
     *                 }
     *             }
     *         })
     *     )
     * )
     */

    public function startupEngagement(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.startup_engagement')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getStartupEngagement($request->validated());
        return $this->success($data);
    }

   /**
     * @OA\Get(
     *     path="/api/dashboard/trends-by-program",
     *     summary="Get trends by program",
     *     operationId="getTrendsByProgram",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Trends grouped by program",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Trends by program retrieved successfully",
     *             "data": {
     *                 {"program": "Startup Sprint", "total": 50},
     *                 {"program": "Growth Launch", "total": 30}
     *             }
     *         })
     *     )
     * )
     */

    public function trendsByProgram(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.trends_by_program')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getTrendsByProgram($request->validated());
        return $this->success($data);
    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/peak-activity",
     *     summary="Get peak activity by weekday",
     *     operationId="getPeakActivity",
     *     tags={"Mentorship Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Peak activity retrieved",
     *         @OA\JsonContent(example={
     *             "success": true,
     *             "message": "Peak activity retrieved successfully",
     *             "data": {
     *                 "Monday": 15,
     *                 "Tuesday": 20,
     *                 "Wednesday": 25,
     *                 "Thursday": 10,
     *                 "Friday": 5
     *             }
     *         })
     *     )
     * )
     */

    public function peakActivity(DashboardFilterRequest $request)
    {
        if(!auth()->user()->ableTo('dashboard.peak_activity')){
            return $this->forbidden(__('api.forbidden'));
        }
        $data = $this->service->getPeakActivity($request->validated());
        return $this->success($data);
    }

/**
 * @OA\Post(
 *     path="/api/mentorship/session-reports",
 *     operationId="getMentorshipSessionReports",
 *     tags={"Mentorship Report"},
 *     summary="Get session reports & recordings for a mentorship session",
 *     description="Returns reports, recordings and summary for a mentorship request. Will auto-fetch data from Zoom/Teams if not present.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=123, description="Mentorship request / session ID"),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Session reports retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="session",
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=123),
 *                     @OA\Property(property="title", type="string", example="Mentorship - Product Strategy"),
 *                     @OA\Property(property="type", type="string", example="mentorship"),
 *                     @OA\Property(property="datetime", type="string", format="date-time", example="2025-09-15T14:00:00Z")
 *                 ),
 *                 @OA\Property(
 *                     property="summary",
 *                     type="object",
 *                     @OA\Property(property="totalDuration", type="number", format="float", example=95),
 *                     @OA\Property(property="totalParticipants", type="integer", example=25),
 *                     @OA\Property(property="totalRecordings", type="integer", example=2)
 *                 ),
 *                 @OA\Property(
 *                     property="reports",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="participant_name", type="string", example="Ahmed Yassin"),
 *                         @OA\Property(property="participant_email", type="string", example="ahmed@example.com"),
 *                         @OA\Property(property="participant_join_date", type="string", format="date-time", example="2025-09-15T14:01:00Z"),
 *                         @OA\Property(property="participant_end_date", type="string", format="date-time", example="2025-09-15T15:30:00Z"),
 *                         @OA\Property(property="attendance_duration", type="number", example=89)
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="recordings",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="recordingType", type="string", example="Zoom Cloud Recording"),
 *                         @OA\Property(property="recordingTime", type="string", format="date-time", example="2025-09-15T14:00:00Z"),
 *                         @OA\Property(property="recordingViewUrl", type="string", example="https://zoom.us/rec/play/..."),
 *                         @OA\Property(property="recordingDownloadUrl", type="string", example="https://zoom.us/rec/download/...")
 *                     )
 *                 ),
 *                 @OA\Property(property="recordingPasscode", type="string", nullable=true, example="z8sKp3")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Missing or invalid session id",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="session_id_required")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Session not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="session_not_found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Server error while fetching session data",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="error_fetching_session_data")
 *         )
 *     )
 * )
 */
    public function sessionReportsData(Request $request)
{
    if (!auth()->user()->ableTo('mentorships.sessions_reports_data')) {
        return $this->forbidden(__('api.forbidden'));
    }

    try {
        $sessionId = $request->input('id');
        if (!$sessionId) {
            return $this->error(__('admin.session_id_required'), 400);
        }

        $session = MentorshipRequest::with(['reports', 'recordings'])
            ->find($sessionId);

        if (!$session) {
            return $this->error(__('admin.session_not_found'), 404);
        }

        // Resource-level ownership: the permission alone does not bind the
        // caller to this session's tenant. Without it, any holder of
        // mentorships.sessions_reports_data reads (and, below, writes) another
        // organization's participant PII and recording URLs.
        // Mentors hold this permission but carry no organization scope, so the
        // session's own mentor is allowed explicitly; nobody else's session is.
        $authUser = auth()->user();
        if (!$authUser->hasRole('admin')
            && !$authUser->ownsOrganization($session->organization_id ?? $session->cycle?->organization_id)
            && (int) $session->mentor_id !== (int) $authUser->id) {
            return $this->error(__('admin.session_not_found'), 404);
        }

        $originalReportsCount = $session->reports->count();
        $originalRecordingsCount = $session->recordings->count();

        // --- Auto-fetch reports (Zoom/Teams)
        if ($originalReportsCount === 0 && $session->zoom_id) {
            try {
                $this->meetingService = OnlineMeetingFactory::create();
                if ($this->meetingService && method_exists($this->meetingService, 'getSessionReport')) {
                    $response = $this->meetingService->getSessionReport($session->zoom_id);

                    $serviceClass = get_class($this->meetingService);
                    $isZoom = stripos($serviceClass, 'zoom') !== false;
                    $isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;

                    if ($isZoom && !empty($response['success']) && !empty($response['data']['participants'])) {
                        foreach ($response['data']['participants'] as $participant) {
                            $joinDate = isset($participant['join_time']) ? Carbon::parse($participant['join_time'])->utc()->toDateTimeString() : null;
                            $endDate  = isset($participant['leave_time']) ? Carbon::parse($participant['leave_time'])->utc()->toDateTimeString() : null;

                            SessionReport::updateOrCreate(
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
                        $session->load('reports');
                    } elseif ($isTeams && !empty($response['participants'])) {
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

                            try { $joinDateParsed = $joinDate ? Carbon::parse($joinDate)->utc()->toDateTimeString() : null; }
                            catch (\Throwable $ex) { $joinDateParsed = null; }

                            try { $leaveDateParsed = $leaveDate ? Carbon::parse($leaveDate)->utc()->toDateTimeString() : null; }
                            catch (\Throwable $ex) { $leaveDateParsed = null; }

                            SessionReport::updateOrCreate(
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
                        $session->load('reports');
                    }
                }
            } catch (\Throwable $e) {
                Log::info('Auto-fetch mentorship reports failed: ' . $e->getMessage());
            }
        }

        // --- Auto-fetch recordings (Zoom/Teams)
        if ($originalRecordingsCount === 0 && $session->zoom_id) {
            try {
                $this->meetingService = OnlineMeetingFactory::create();
                if ($this->meetingService && method_exists($this->meetingService, 'getSessionRecord')) {
                    $response = $this->meetingService->getSessionRecord($session->zoom_id);

                    $serviceClass = get_class($this->meetingService);
                    $isZoom = stripos($serviceClass, 'zoom') !== false;
                    $isTeams = stripos($serviceClass, 'team') !== false || stripos($serviceClass, 'graph') !== false;

                    if ($isZoom && !empty($response['data']['recording_files'])) {
                        foreach ($response['data']['recording_files'] as $file) {
                            $recordingTime = isset($file['recording_start']) ? Carbon::parse($file['recording_start'])->utc()->toDateTimeString() : null;
                            $password = $response['data']['password'] ?? null;

                            SessionRecording::updateOrCreate(
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
                        $session->load('recordings');
                    } elseif ($isTeams && !empty($response['data'][0])) {
                        $file = $response['data'][0];
                        $recordingId = $file['id'] ?? null;
                        $recordingUrl = $file['recordingContentUrl'] ?? $file['meetingId'] ?? null;
                        $createdDateTime = isset($file['createdDateTime']) ? Carbon::parse($file['createdDateTime'])->utc()->toDateTimeString() : null;

                        if ($recordingId) {
                            SessionRecording::updateOrCreate(
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
                            $session->load('recordings');
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::info('Auto-fetch mentorship recordings failed: ' . $e->getMessage());
            }
        }

        $recordings = $session->recordings->map(function ($recording) {
            $time = $recording->recording_time;
            if ($time) {
                try { $time = Carbon::parse($time, 'UTC')->toISOString(); }
                catch (\Exception $e) { $time = null; }
            }
            return [
                'recordingType' => $recording->recording_type,
                'recordingTime' => $time,
                'recordingViewUrl' => $recording->recording_view_url,
                'recordingDownloadUrl' => $recording->recording_download_url,
            ];
        });

        $reports = $session->reports->map(function ($report) {
            $joinDate = $report->participant_join_date;
            if ($joinDate) {
                try { $joinDate = Carbon::parse($joinDate, 'UTC')->toISOString(); }
                catch (\Exception $e) { $joinDate = null; }
            }
            $endDate = $report->participant_end_date;
            if ($endDate) {
                try { $endDate = Carbon::parse($endDate, 'UTC')->toISOString(); }
                catch (\Exception $e) { $endDate = null; }
            }
            return [
                'participant_name' => $report->participant_name,
                'participant_email' => $report->participant_email,
                'participant_join_date' => $joinDate,
                'participant_end_date' => $endDate,
                'attendance_duration' => $report->attendance_duration,
            ];
        });

        $totalDuration = $session->reports->sum('attendance_duration');
        $totalParticipants = $session->reports->count();
        $totalRecordings = $session->recordings->count();
        $recordingPasscode = $session->recordings->first()->passcode ?? null;

        $payload = [
            'session' => [
                'id' => $session->id,
                'title' => $session->comment ?? 'Mentorship Session',
                'type' => 'mentorship',
                'datetime' => $session->zoom_date,
            ],
            'summary' => [
                'totalDuration' => $totalDuration,
                'totalParticipants' => $totalParticipants,
                'totalRecordings' => $totalRecordings,
            ],
            'reports' => $reports,
            'recordings' => $recordings,
            'recordingPasscode' => $recordingPasscode,
        ];

        $debug = [
            'sessionId' => $sessionId,
            'meetingId' => $session->zoom_id,
            'reportsCountBefore' => $originalReportsCount,
            'recordingsCountBefore' => $originalRecordingsCount,
            'reportsCountAfter' => $session->reports->count(),
            'recordingsCountAfter' => $session->recordings->count(),
        ];

        return $this->success($payload + ['debug' => $debug]);
    } catch (\Throwable $e) {
        Log::error('Error in MentorshipReportController@sessionReportsData: ' . $e->getMessage());
        return $this->error(__('admin.error_fetching_session_data'), 500);
    }
}
}
