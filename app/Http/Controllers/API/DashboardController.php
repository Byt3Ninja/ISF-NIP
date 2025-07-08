<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/dashboard",
 *     summary="Get mentor dashboard data",
 *     tags={"Dashboard"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Dashboard data returned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="coming_sessions_count", type="integer", example=2),
 *             @OA\Property(
 *                 property="current_schedule",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="days", type="array", @OA\Items(type="string"), example={"Monday", "Wednesday"}),
 *                     @OA\Property(property="tfrom", type="string", example="09:00"),
 *                     @OA\Property(property="tto", type="string", example="17:00"),
 *                     @OA\Property(property="notes", type="string", example="Available for mentoring")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="next_sessions",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="company_logo", type="string", example="https://example.com/logo.png"),
 *                     @OA\Property(property="company", type="string", example="TechCorp"),
 *                     @OA\Property(property="user", type="string", example="John Doe"),
 *                     @OA\Property(property="date", type="string", example="2025-07-01 14:00"),
 *                     @OA\Property(property="admin_notes", type="string", example="Important session"),
 *                     @OA\Property(property="meeting_link", type="string", example="https://zoom.us/j/123456789")
 *                 )
 *             ),
 *             @OA\Property(property="completedTasks", type="integer", example=5),
 *             @OA\Property(property="nonCompletedTasks", type="integer", example=2),
 *             @OA\Property(property="sessionWithoutTasks", type="integer", example=3),
 *             @OA\Property(property="startupAttendanceRate", type="string", example="85%")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mentorId = Auth::id();

        $comingSessionsCount = DB::table('mentorship_requests')
            ->where('mentor_id', $mentorId)
            ->whereNotNull('zoom_date')
            ->where('approved', 'yes')
            ->where('zoom_date', '>=', Carbon::now())
            ->count();
        
      $schedules = DB::table('schedules')
        ->where('user_id', $mentorId)
        ->where('active', 1)
        ->select('days', 'tfrom', 'tto', 'notes')
        ->get()
        ->map(function ($schedule) {
        return [
            'days' => json_decode($schedule->days, true) ?: $schedule->days,
            'tfrom' => $schedule->tfrom,
            'tto' => $schedule->tto,
            'notes' => $schedule->notes,
        ];
    });

        $nextSessions = DB::table('mentorship_requests as mr')
            ->join('users as u', 'u.id', '=', 'mr.user_id')
            ->leftJoin('companies as c', 'c.id', '=', 'u.company_id')
            ->where('mr.mentor_id', $mentorId)
            ->whereNotNull('mr.zoom_date')
            ->where('mr.approved', 'yes')
            ->where('mr.zoom_date', '>=', Carbon::now())
            ->orderBy('mr.zoom_date', 'asc')
            ->select(
                'mr.zoom_date as date',
                'mr.admin_comment as admin_notes',
                'mr.join_url as meeting_link',
                'u.first_name_en',
                'u.last_name_en',
                'c.name_en as company',
                'c.logo as company_logo'
            )
            ->get()
            ->map(function ($session) {
                return [
                    'companyLogo' => $session->company_logo,
                    'company' => $session->company,
                    'user' => trim($session->first_name_en . ' ' . $session->last_name_en),
                    'date' => Carbon::parse($session->date)->format('Y-m-d H:i'),
                    'adminNotes' => $session->admin_notes,
                    'meetingLink' => $session->meeting_link,
                ];
            });

        $tasks = DB::table('mentorship_requests as mr')
            ->leftJoin('mentorship_tasks as mt', 'mt.mentorship_request_id', '=', 'mr.id')
            ->whereNull('mr.deleted_at')
            ->where('mr.mentor_id', $mentorId)
            ->select(
                'mr.id as request_id',
                'mt.id as task_id',
                'mt.title as task_title',
                'mt.status as task_status'
            )
            ->get();

        $sessionWithoutTasks = $tasks->whereNull('task_id')->values()->count();
        $completedTasks = $tasks->where('task_status', 'completed')->values()->count();
        $nonCompletedTasks = $tasks->where('task_status', 'pending')->values()->count();

        $completedSessions = DB::table('mentorship_requests')
        ->where('mentor_id', $mentorId)
        ->whereNull('deleted_at')
        ->whereNotNull('start_url')
        ->whereDate('zoom_date', '<=', now()->toDateString())
        ->select('done')
        ->get();

      $total = $completedSessions->count();
      $attended = $completedSessions->sum('done');
      $attendsRate = $total > 0 ? round(($attended / $total) * 100, 2) . '%' : '0%';

        return response()->json([
            'success' => true,
            'comingSessionsCount' => $comingSessionsCount,
            'currentSchedule' => $schedules,
            'nextSessions' => $nextSessions,
            'completedTasks' => $completedTasks,
            'nonCompletedTasks' => $nonCompletedTasks,
            'sessionWithoutTasks' => $sessionWithoutTasks,
            'startupAttendanceRate' => $attendsRate,
        ]);
    }
}
