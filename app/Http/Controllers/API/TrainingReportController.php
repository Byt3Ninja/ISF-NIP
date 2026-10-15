<?php
namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\SessionReport;
use App\TrainingSession;
use App\SessionRecording;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Factories\OnlineMeetingFactory;
use App\Services\TrainingReportService;
use App\Http\Requests\TrainingReportFilterRequest;

class TrainingReportController extends Controller
{
    use ApiResponder;

    protected $service;
    protected $meetingService;

    public function __construct(TrainingReportService $service)
    {
        $this->service = $service;
    }

/**
 * @OA\Get(
 *     path="/api/training/summary",
 *     tags={"Training Reports"},
 *     summary="Get overall training KPIs summary",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             example={
 *                 "totalSessions": 106,
 *                 "totalHours": 59.5,
 *                 "zoomSessions": 35,
 *                 "onlineSessions": 52,
 *                 "offlineSessions": 19,
 *                 "zoomShare": 33,
 *                 "avgPerSession": 0.56,
 *                 "completionRate": 84,
 *                 "avgAttendance": 78
 *             }
 *         )
 *     )
 * )
 */
    public function summary(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.summary')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getSummary($request->validated()));
    }

/**
 * @OA\Get(
 *     path="/api/training/session-types",
 *     tags={"Training Reports"},
 *     summary="Get breakdown of training session types",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(example={"online": 52, "offline": 19, "sessions": 35})
 *     )
 * )
 */

    public function sessionTypes(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.session_types')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getSessionTypes($request->validated()));
    }

/**
 * @OA\Get(
 *     path="/api/training/monthly-trends",
 *     tags={"Training Reports"},
 *     summary="Get monthly session counts",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(example={"Jan": 14, "Feb": 16, "Mar": 18})
 *     )
 * )
 */

    public function monthlyTrends(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.monthly_trends')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getMonthlyTrends($request->validated()));
    }
    /**
 * @OA\Get(
 *     path="/api/training/type-breakdown",
 *     tags={"Training Reports"},
 *     summary="Get session type distribution",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             example={
 *                 "labels": {"Zoom", "Online", "Offline"},
 *                 "counts": {35, 17, 19}
 *             }
 *         )
 *     )
 * )
 */


    public function typeBreakdown(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.type_breakdown')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getTypeBreakdown($request->validated()));
    }
/**
 * @OA\Get(
 *     path="/api/training/hours-trend",
 *     tags={"Training Reports"},
 *     summary="Get trend of training hours by month",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(example={"Jan": 4.5, "Feb": 9.75, "Mar": 5.25})
 *     )
 * )
 */

    public function hoursTrend(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.hours_trend')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getHoursTrend($request->validated()));
    }

/**
 * @OA\Get(
 *     path="/api/training/trainers",
 *     tags={"Training Reports"},
 *     summary="Get trainer performance data",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 example={
 *                     "name": "A. Nabil",
 *                     "sessions": 18,
 *                     "hours": 10.5,
 *                     "attendees": 27
 *                 }
 *             )
 *         )
 *     )
 * )
 */

    public function trainers(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.trainers')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getTrainerPerformance($request->validated()));
    }

/**
 * @OA\Get(
 *     path="/api/training/topics",
 *     tags={"Training Reports"},
 *     summary="Get topic-based analysis",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 example={
 *                     "name": "Mentorship Skills",
 *                     "sessions": 32,
 *                     "avgAttendees": 28,
 *                     "completion": 91
 *                 }
 *             )
 *         )
 *     )
 * )
 */

    public function topics(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.topics')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getTopics($request->validated()));
    }

/**
 * @OA\Get(
 *     path="/api/training/sessions/recent",
 *     tags={"Training Reports"},
 *     summary="Get recent training sessions",
 *     @OA\Parameter(
 *         name="program",
 *         in="query",
 *         required=false,
 *         description="Filter by program title",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date in YYYY-MM-DD format",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 example={
 *                     "id": 1,
 *                     "date": "2025-09-15 14:00",
 *                     "title": "Business Model Canvas",
 *                     "type": "Zoom",
 *                     "trainer": "Ahmed Yassin",
 *                     "attendees": 25,
 *                     "duration": 1.5
 *                 }
 *             )
 *         )
 *     )
 * )
 */

    public function recentSessions(TrainingReportFilterRequest $request)
    {
        if(!auth()->user()->ableTo('training.recent_sessions')){
            return $this->forbidden(__('api.forbidden'));
        }
        return $this->success($this->service->getRecentSessions($request->validated()));
    }

    /**
 * @OA\Post(
 *     path="/api/training/session-reports",
 *     operationId="getTrainingSessionReports",
 *     tags={"Training Reports"},
 *     summary="Get session reports & recordings for a training session",
 *     description="Returns reports, recordings and summary for a training session. Will auto-fetch data from Zoom/Teams if not present.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=123, description="Training session ID")
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
 *                     @OA\Property(property="title", type="string", example="Business Model Canvas"),
 *                     @OA\Property(property="type", type="string", example="sessions"),
 *                     @OA\Property(property="datetime", type="string", format="date-time", example="2025-09-15T14:00:00Z"),
 *                     @OA\Property(property="enddatetime", type="string", format="date-time", example="2025-09-15T15:30:00Z")
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
 *                         @OA\Property(property="participantName", type="string", example="Ahmed Yassin"),
 *                         @OA\Property(property="participantEmail", type="string", example="ahmed@example.com"),
 *                         @OA\Property(property="participantJoinDate", type="string", format="date-time", example="2025-09-15T14:01:00Z"),
 *                         @OA\Property(property="participantEndDate", type="string", format="date-time", example="2025-09-15T15:30:00Z"),
 *                         @OA\Property(property="attendanceDuration", type="number", example=89)
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
 *             ),
 *             @OA\Property(
 *                 property="debug",
 *                 type="object",
 *                 @OA\Property(property="sessionId", type="integer", example=123),
 *                 @OA\Property(property="meetingId", type="string", example="832001"),
 *                 @OA\Property(property="sessionType", type="string", example="sessions"),
 *                 @OA\Property(property="reportsCountBefore", type="integer", example=0),
 *                 @OA\Property(property="recordingsCountBefore", type="integer", example=0),
 *                 @OA\Property(property="reportsCountAfter", type="integer", example=25),
 *                 @OA\Property(property="recordingsCountAfter", type="integer", example=2),
 *                 @OA\Property(property="autoFetchReportsAttempted", type="boolean", example=true),
 *                 @OA\Property(property="autoFetchRecordingsAttempted", type="boolean", example=true)
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
    public function getSessionReportsData(Request $request)
{
    if (!auth()->user()->ableTo('trainings.session_reports_data')) {
        return $this->forbidden(__('api.forbidden'));
    }

    try {
        $sessionId = $request->input('id');
        if (!$sessionId) {
            return $this->error(__('admin.session_id_required'), 400);
        }

        $session = TrainingSession::with(['reports', 'recordings', 'step.cycle'])
            ->find($sessionId);

        if (!$session) {
            return $this->error(__('admin.session_not_found'), 404);
        }

        // Resource-level ownership: the permission alone does not bind the
        // caller to this session's tenant. Without it, any holder of
        // trainings.session_reports_data reads (and, below, writes) another
        // organization's attendee PII and recording URLs.
        $authUser = auth()->user();
        if (!$authUser->hasRole('admin')
            && !$authUser->ownsOrganization($session->step?->cycle?->organization_id)) {
            return $this->error(__('admin.session_not_found'), 404);
        }

        $originalReportsCount = $session->reports->count();
        $originalRecordingsCount = $session->recordings->count();

        // --- Auto-fetch reports (Zoom/Teams)
        if ($originalReportsCount === 0 && $session->type === 'sessions' && $session->zoom_id) {
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
                        $session->load('reports');
                    } elseif ($isTeams && !empty($response['participants'])) {
                        foreach ($response['participants'] as $p) {
                            $participantName = $p['displayName'] ?? $p['identity']['user']['displayName'] ?? null;
                            $participantEmail = $p['identity']['user']['email'] ?? null;
                            $participantUserId = $p['identity']['user']['id'] ?? null;
                            $duration = isset($p['totalAttendanceInSeconds']) ? $p['totalAttendanceInSeconds'] / 60 : 0;

                            $interval = $p['attendanceIntervals'][0] ?? null;
                            $joinDate = $interval['joinDateTime'] ?? $interval['joinDate'] ?? null;
                            $leaveDate = $interval['leaveDateTime'] ?? $interval['leaveDate'] ?? null;

                            try { $joinDateParsed = $joinDate ? Carbon::parse($joinDate)->utc()->toDateTimeString() : null; }
                            catch (\Throwable $ex) { $joinDateParsed = null; }

                            try { $leaveDateParsed = $leaveDate ? Carbon::parse($leaveDate)->utc()->toDateTimeString() : null; }
                            catch (\Throwable $ex) { $leaveDateParsed = null; }

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
                        $session->load('reports');
                    }
                }
            } catch (\Throwable $e) {
                Log::info('Auto-fetch reports failed: ' . $e->getMessage());
            }
        }

        // --- Auto-fetch recordings (Zoom/Teams)
        if ($originalRecordingsCount === 0 && $session->type === 'sessions' && $session->zoom_id) {
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
                                    'session_id' => $session->id,
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
                                    'session_id' => $session->id,
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
                Log::info('Auto-fetch recordings failed: ' . $e->getMessage());
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
                'participantName' => $report->participant_name,
                'participantEmail' => $report->participant_email,
                'participantJoinDate' => $joinDate,
                'participantEndDate' => $endDate,
                'attendanceDuration' => $report->attendance_duration,
            ];
        });

        $totalDuration = $session->reports->sum('attendance_duration');
        $totalParticipants = $session->reports->count();
        $totalRecordings = $session->recordings->count();
        $recordingPasscode = $session->recordings->first()->passcode ?? null;

        $payload = [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'type' => $session->type,
                'datetime' => $session->datetime,
                'enddatetime' => $session->enddatetime,
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
            'sessionType' => $session->type,
            'reportsCountBefore' => $originalReportsCount,
            'recordingsCountBefore' => $originalRecordingsCount,
            'reportsCountAfter' => $session->reports->count(),
            'recordingsCountAfter' => $session->recordings->count(),
            'autoFetchReportsAttempted' => $originalReportsCount === 0 && $session->type === 'sessions' && $session->zoom_id,
            'autoFetchRecordingsAttempted' => $originalRecordingsCount === 0 && $session->type === 'sessions' && $session->zoom_id,
        ];

        return $this->success($payload + ['debug' => $debug]);

    } catch (\Throwable $e) {
        return $this->error(__('admin.error_fetching_session_data'));
    }
}
}
