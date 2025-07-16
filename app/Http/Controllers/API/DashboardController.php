<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Get(
 *     path="/api/dashboard",
 *     summary="Get dashboard data for Admin or Mentor",
 *     tags={"Dashboard"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Dashboard data returned successfully",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     description="Admin Dashboard Response",
 *                     @OA\Property(property="totalUsers", type="integer", example=150),
 *                     @OA\Property(property="totalUsersChange", type="number", format="float", example=5.4),
 *                     @OA\Property(property="totalSessions", type="integer", example=200),
 *                     @OA\Property(property="totalSessionsChange", type="number", format="float", example=3.1),
 *                     @OA\Property(property="sessionsDuration", type="string", example="540.5h"),
 *                     @OA\Property(property="sessionsDurationChange", type="number", format="float", example=2.7),
 *                     @OA\Property(
 *                         property="startupAttendanceRate",
 *                         type="object",
 *                         @OA\Property(property="rate", type="number", format="float", example=85.5)
 *                     ),
 *                     @OA\Property(
 *                         property="completedTasks",
 *                         type="object",
 *                         @OA\Property(property="rate", type="number", format="float", example=60.3)
 *                     ),
 *                     @OA\Property(
 *                         property="screening",
 *                         type="object",
 *                         @OA\Property(property="first", type="integer", example=40),
 *                         @OA\Property(property="final", type="integer", example=25),
 *                         @OA\Property(property="total", type="integer", example=65)
 *                     ),
 *                     @OA\Property(
 *                         property="userRoles",
 *                         type="object",
 *                         @OA\Property(property="mentors", type="integer", example=20),
 *                         @OA\Property(property="registered", type="integer", example=100)
 *                     ),
 *                     @OA\Property(
 *                         property="userGender",
 *                         type="object",
 *                         @OA\Property(property="male", type="integer", example=70),
 *                         @OA\Property(property="female", type="integer", example=80),
 *                         @OA\Property(property="malePercentage", type="number", format="float", example=46.7),
 *                         @OA\Property(property="femalePercentage", type="number", format="float", example=53.3),
 *                         @OA\Property(property="change", type="number", format="float", example=10.5)
 *                     ),
 *                     @OA\Property(
 *                         property="metadata",
 *                         type="object",
 *                         @OA\Property(property="lastUpdated", type="string", example="03:15:20 PM")
 *                     )
 *                 ),
 *                 @OA\Schema(
 *                     description="Mentor Dashboard Response",
 *                     @OA\Property(property="comingSessionsCount", type="integer", example=2),
 *                     @OA\Property(
 *                         property="currentSchedule",
 *                         type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="days", type="array", @OA\Items(type="string"), example={"Monday", "Wednesday"}),
 *                             @OA\Property(property="tfrom", type="string", example="09:00"),
 *                             @OA\Property(property="tto", type="string", example="17:00"),
 *                             @OA\Property(property="notes", type="string", example="Available for mentoring")
 *                         )
 *                     ),
 *                     @OA\Property(
 *                         property="nextSessions",
 *                         type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="companyLogo", type="string", example="https://example.com/logo.png"),
 *                             @OA\Property(property="company", type="string", example="TechCorp"),
 *                             @OA\Property(property="user", type="string", example="John Doe"),
 *                             @OA\Property(property="date", type="string", example="2025-07-01 14:00"),
 *                             @OA\Property(property="adminNotes", type="string", example="Important session"),
 *                             @OA\Property(property="meetingLink", type="string", example="https://zoom.us/j/123456789")
 *                         )
 *                     ),
 *                     @OA\Property(property="completedTasks", type="integer", example=5),
 *                     @OA\Property(property="nonCompletedTasks", type="integer", example=2),
 *                     @OA\Property(property="sessionWithoutTasks", type="integer", example=3),
 *                     @OA\Property(property="startupAttendanceRate", type="string", example="85%")
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized dashboard access"
 *     )
 * )
 */
class DashboardController extends Controller
{
    use ApiResponder;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = Auth::user();
        $locale = app()->getLocale();
        $nameColumn = $locale === 'ar' ? 'name_ar' : 'name_en';

        if ($user->hasRole('Admin')) {
            $data = $this->dashboardService->getAdminDashboardData($nameColumn);
            return $this->success($data);
        }

        if ($user->hasRole('Mentor')) {
            $data = $this->dashboardService->getMentorDashboardData($user->id);
            return $this->success($data);
        }

    }
}
