<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;
use App\Services\MentorshipReportService;

class MentorshipReportController extends Controller
{
    use ApiResponder;
    protected $service;

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
}
