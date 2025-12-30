<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use App\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Get(
 *     path="/api/dashboard",
 *     summary="Retrieve dashboard data for Admin, Organization Manager, Judger, Mentor, or Startup",
 *     tags={"Dashboard"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Dashboard data retrieved successfully",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     title="Admin Dashboard",
 *                     @OA\Property(property="totalUsers", type="integer", example=150),
 *                     @OA\Property(property="totalUsersChange", type="number", format="float", example=5.4),
 *                     @OA\Property(property="totalSessions", type="integer", example=200),
 *                     @OA\Property(property="totalSessionsChange", type="number", format="float", example=3.1),
 *                     @OA\Property(property="sessionsDuration", type="string", example="540.5h"),
 *                     @OA\Property(property="sessionsDurationChange", type="number", format="float", example=2.7),
 *                     @OA\Property(property="startupAttendanceRate", type="object",
 *                         @OA\Property(property="rate", type="number", format="float", example=85.5)
 *                     ),
 *                     @OA\Property(property="completedTasks", type="object",
 *                         @OA\Property(property="rate", type="number", format="float", example=60.3)
 *                     ),
 *                     @OA\Property(property="screening", type="object",
 *                         @OA\Property(property="first", type="integer", example=40),
 *                         @OA\Property(property="final", type="integer", example=25),
 *                         @OA\Property(property="total", type="integer", example=65)
 *                     ),
 *                     @OA\Property(property="userRoles", type="object",
 *                         @OA\Property(property="mentors", type="integer", example=20),
 *                         @OA\Property(property="registered", type="integer", example=100)
 *                     ),
 *                     @OA\Property(property="userGender", type="object",
 *                         @OA\Property(property="male", type="integer", example=70),
 *                         @OA\Property(property="female", type="integer", example=80),
 *                         @OA\Property(property="malePercentage", type="number", format="float", example=46.7),
 *                         @OA\Property(property="femalePercentage", type="number", format="float", example=53.3),
 *                         @OA\Property(property="change", type="number", format="float", example=10.5)
 *                     ),
 *                     @OA\Property(property="metadata", type="object",
 *                         @OA\Property(property="lastUpdated", type="string", example="03:15:20 PM")
 *                     )
 *                 ),
 *                 @OA\Schema(
 *                     title="Mentor Dashboard",
 *                     @OA\Property(property="comingSessionsCount", type="integer", example=2),
 *                     @OA\Property(property="currentSchedule", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="days", type="array", @OA\Items(type="string"), example={"Monday", "Wednesday"}),
 *                             @OA\Property(property="tfrom", type="string", example="09:00"),
 *                             @OA\Property(property="tto", type="string", example="17:00"),
 *                             @OA\Property(property="notes", type="string", example="Available for mentoring")
 *                         )
 *                     ),
 *                     @OA\Property(property="nextSessions", type="array",
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
 *                     @OA\Property(property="startupAttendanceRate", type="string", example="85%"),
 *                     @OA\Property(property="mentorAverageRating", type="number", format="float", example=4.5),
 *                     @OA\Property(property="totalMentorshipHours", type="number", format="float", example=42.5),
 *                     @OA\Property(property="totalMentorshipSessions", type="integer", example=18),
 *  *                     @OA\Property(
 *                         property="sessionsSummary",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="month", type="string", example="Jan"),
 *                             @OA\Property(property="sessions", type="integer", example=3),
 *                             @OA\Property(property="hours", type="number", format="float", example=5.5)
 *                         )
 *                     ),
 *                 ),
 *                 @OA\Schema(
 *                     title="Judger Dashboard",
 *                     @OA\Property(property="screening", type="integer", example=10),
 *                     @OA\Property(property="finalScreening", type="integer", example=5),
 *                     @OA\Property(property="judgingList", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="stepTitle", type="string", example="Initial Screening"),
 *                             @OA\Property(property="stepFrom", type="string", example="2025-06-01"),
 *                             @OA\Property(property="stepTo", type="string", example="2025-06-30"),
 *                             @OA\Property(property="stepDesc", type="string", example="Evaluate startups in the first round."),
 *                             @OA\Property(property="cycleTitle", type="string", example="Summer Accelerator 2025"),
 *                             @OA\Property(property="cycleStart", type="string", example="2025-05-01"),
 *                             @OA\Property(property="cycleEnd", type="string", example="2025-08-01"),
 *                             @OA\Property(property="companies", type="array",
 *                                 @OA\Items(
 *                                     @OA\Property(property="startupLogo", type="string", example="https://example.com/logo.png"),
 *                                     @OA\Property(property="startupName", type="string", example="TechStart"),
 *                                     @OA\Property(property="imgUrl", type="string", example="https://example.com/user.png"),
 *                                     @OA\Property(property="userName", type="string", example="Sarah Johnson"),
 *                                     @OA\Property(property="userTitle", type="string", example="CEO"),
 *                                     @OA\Property(property="evaluationStatus", type="string", example="completed")
 *                                 )
 *                             )
 *                         )
 *                     )
 *                 ),
 *                 @OA\Schema(
 *                     title="Startup Dashboard",
 *                     @OA\Property(property="program", type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Summer Accelerator"),
 *                         @OA\Property(property="description", type="string", example="A 3-month program for early-stage startups."),
 *                         @OA\Property(property="startDate", type="string", example="2025-05-01"),
 *                         @OA\Property(property="endDate", type="string", example="2025-08-01"),
 *                         @OA\Property(property="currentStep", type="integer", example=3),
 *                         @OA\Property(property="totalSteps", type="integer", example=5),
 *                         @OA\Property(property="progress", type="string", example="60%")
 *                     ),
 *                     @OA\Property(property="programSteps", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=2),
 *                             @OA\Property(property="name", type="string", example="Product Development"),
 *                             @OA\Property(property="description", type="string", example="Refining your MVP"),
 *                             @OA\Property(property="dueDate", type="string", example="20-07-2025"),
 *                             @OA\Property(property="status", type="string", example="Started"),
 *                             @OA\Property(property="completedDate", type="string", example="15-07-2025")
 *                         )
 *                     ),
 *                     @OA\Property(property="mentors", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=5),
 *                             @OA\Property(property="name", type="string", example="Jane Smith"),
 *                             @OA\Property(property="email", type="string", example="jane@example.com"),
 *                             @OA\Property(property="title", type="string", example="Product Coach"),
 *                             @OA\Property(property="imgUrl", type="string", example="https://example.com/jane.jpg"),
 *                             @OA\Property(property="bio", type="string", example="Experienced in agile and product."),
 *                             @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/janesmith"),
 *                             @OA\Property(property="specialization", type="string", example="Product Strategy"),
 *                             @OA\Property(property="mentorExpertises", type="array", @OA\Items(type="string"), example={"Product", "Agile"}),
 *                             @OA\Property(property="rating", type="number", format="float", example=4.7),
 *                             @OA\Property(property="programCount", type="integer", example=3),
 *                             @OA\Property(property="programIds", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
 *                             @OA\Property(property="role", type="string", example="mentor")
 *                         )
 *                     ),
 *                     @OA\Property(property="availableSurveys", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=12),
 *                             @OA\Property(property="triggeredOn", type="string", example="mentorship"),
 *                             @OA\Property(property="triggeredBy", type="string", example="session"),
 *                             @OA\Property(property="title", type="string", example="Mentorship Session Feedback"),
 *                             @OA\Property(property="mentorName", type="string", example="Sarah Johnson")
 *                         )
 *                     ),
 *                     @OA\Property(property="mentorshipSessions", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="mentorId", type="integer", example=3),
 *                             @OA\Property(property="mentorName", type="string", example="Ahmed Farouk"),
 *                             @OA\Property(property="imgUrl", type="string", example="https://example.com/ahmed.jpg"),
 *                             @OA\Property(property="date", type="string", example="2025-07-25"),
 *                             @OA\Property(property="time", type="string", example="14:30"),
 *                             @OA\Property(property="duration", type="integer", example=60),
 *                             @OA\Property(property="adminNotes", type="string", example="Discuss MVP goals"),
 *                             @OA\Property(property="meetingLink", type="string", example="https://zoom.us/j/123456789")
 *                         )
 *                     ),
 *                     @OA\Property(property="latestServices", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="serviceId", type="integer", example=7),
 *                             @OA\Property(property="serviceName", type="string", example="Legal Consultation"),
 *                             @OA\Property(property="serviceType", type="string", example="Legal")
 *                         )
 *                     ),
 *                     @OA\Property(property="services", type="integer", example=8),
 *                     @OA\Property(property="mentorCount", type="integer", example=12),
 *                     @OA\Property(property="completedTasks", type="integer", example=5),
 *                     @OA\Property(property="mentorAvailabilitySlots", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=22),
 *                             @OA\Property(property="mentorId", type="integer", example=3),
 *                             @OA\Property(property="mentorName", type="string", example="Ahmed Farouk"),
 *                             @OA\Property(property="imgUrl", type="string", example="https://example.com/ahmed.jpg"),
 *                             @OA\Property(property="type", type="string", example="weekly"),
 *                             @OA\Property(property="days", type="array", @OA\Items(type="string"), example={"Sunday", "Tuesday"}),
 *                             @OA\Property(property="start", type="string", example="10:00"),
 *                             @OA\Property(property="end", type="string", example="12:00"),
 *                             @OA\Property(property="notes", type="string", example="Available for UI/UX mentoring"),
 *                             @OA\Property(property="availability", type="boolean", example=true)
 *                         )
 *                     ),
 *                     @OA\Property(property="latestEvents", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=15),
 *                             @OA\Property(property="title", type="string", example="Demo Day"),
 *                             @OA\Property(property="date", type="string", example="2025-07-30"),
 *                             @OA\Property(property="time", type="string", example="13:00:00"),
 *                             @OA\Property(property="allDay", type="boolean", example=false)
 *                         )
 *                     ),
 *                     @OA\Property(property="analytics", type="object",
 *                         @OA\Property(property="programProgress", type="string", example="60%"),
 *                         @OA\Property(property="tasksCompleted", type="integer", example=5),
 *                         @OA\Property(property="totalTasks", type="integer", example=8),
 *                         @OA\Property(property="mentorshipHours", type="number", format="float", example=9.5)
 *                     )
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
            $data = $this->dashboardService->adminDashboard($nameColumn);
            return $this->success($data);
        }

        elseif ($user->hasRole('Mentor')) {
            $data = $this->dashboardService->mentorDashboard($user->id);
            return $this->success($data);
        }
        elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
            $organizationId = !empty($orgIds) ? $orgIds[0] : null;
            if ($organizationId) {
                $data = $this->dashboardService->organizationManagerDashboard($organizationId, $nameColumn);
                return $this->success($data);
            }
            return $this->forbidden(__('api.forbidden'));
        }
        elseif ($user->hasRole('Judger')) {
            $data = $this->dashboardService->judgerDashboard($nameColumn);
            return $this->success($data);
        }
        elseif ($user->hasRole('Registered')) {
            $data = $this->dashboardService->startupDashboard($user->id);
            return $this->success($data);
        }
        else{
            return $this->forbidden(__('api.forbidden'));
        }
        

    }
}
