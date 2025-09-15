<?php
namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingReportFilterRequest;
use App\Services\TrainingReportService;

class TrainingReportController extends Controller
{
    use ApiResponder;

    protected $service;

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
}
