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
        $user = Auth::user();
        $locale = app()->getLocale();
        $nameColumn = $locale === 'ar' ? 'name_ar' : 'name_en';
        if ($user->hasRole('Admin')) {
            $totalUsers = DB::table('users')->count();
            $prevTotalUsers = DB::table('users')->whereDate('created_at', '<', Carbon::now()->subMonth())->count();
            $userChange = $prevTotalUsers > 0 ? round((($totalUsers - $prevTotalUsers) / $prevTotalUsers) * 100, 1) : 0;

            $totalSessions = DB::table('training_sessions')->count();
            $prevTotalSessions = DB::table('training_sessions')->whereDate('created_at', '<', Carbon::now()->subMonth())->count();
            $sessionsChange = $prevTotalSessions > 0 ? round((($totalSessions - $prevTotalSessions) / $prevTotalSessions) * 100, 1) : 0;

            $totalDuration = DB::table('training_sessions')->sum('duration');
            $prevDuration = DB::table('training_sessions')->whereDate('created_at', '<', Carbon::now()->subMonth())->sum('duration');
            $durationChange = $prevDuration > 0 ? round((($totalDuration - $prevDuration) / $prevDuration) * 100, 1) : 0;

            $screeningFirst = DB::table('screenings')->count();
            $screeningFinal = DB::table('finalscreenings')->count();

            $roles = DB::table('roles')->pluck('name', 'id')->toArray();
            $roleCounts = [];
            foreach (['Registered', 'Mentor', 'Judger', 'organizationManager'] as $key) {
                $roleId = array_search($key, $roles);
                $count = DB::table('model_has_roles')->where('role_id', $roleId)->count();
                $roleCounts[strtolower(str_replace(' ', '', $key)) . 's'] = $count;
            }

            $genderStats = DB::table('users')
                ->selectRaw("gender, COUNT(*) as count")
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();

            $male = $genderStats['Male'] ?? 0;
            $female = $genderStats['Female'] ?? 0;
            $totalGender = $male + $female;
            $genderChange = $totalGender > 0 ? round((($totalGender - 63) / 63) * 100, 1) : 0;

            $totalCompanies = DB::table('companies')->count();

            $topSectors = DB::table('companies')
                ->select('sector', DB::raw('COUNT(*) as count'))
                ->whereNotNull('sector')
                ->groupBy('sector')
                ->orderByDesc('count')
                ->get();

            $developmentStages = DB::table('companies')
                ->select('development_stage', DB::raw('COUNT(*) as count'))
                ->whereNotNull('development_stage')
                ->groupBy('development_stage')
                ->get();

            $businessModels = DB::table('companies')
                ->select('business_model', DB::raw('COUNT(*) as count'))
                ->whereNotNull('business_model')
                ->groupBy('business_model')
                ->get();

            $targetMarkets = DB::table('companies')
                ->select('targeted_market', DB::raw('COUNT(*) as count'))
                ->whereNotNull('targeted_market')
                ->groupBy('targeted_market')
                ->get();

            $states = DB::table('companies')
                ->leftJoin('states', 'states.id', '=', 'companies.state_id')
                ->select("states.{$nameColumn} as state", DB::raw('COUNT(companies.id) as count'))
                ->groupBy("states.{$nameColumn}")
                ->get();

            $countries = DB::table('companies')
                ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
                ->select("countries.{$nameColumn} as country", DB::raw('COUNT(companies.id) as count'))
                ->groupBy("countries.{$nameColumn}")
                ->get();

            $sdgs = DB::table('companies')
                ->select('Sustainable_development', DB::raw('COUNT(*) as count'))
                ->whereNotNull('Sustainable_development')
                ->groupBy('Sustainable_development')
                ->get();

                $stateCounts = DB::table('users')
                ->leftJoin('states', 'states.id', '=', 'users.state_id')
                ->select("states.{$nameColumn} as state", DB::raw('COUNT(users.id) as count'))
                ->whereNotNull('users.state_id')
                ->groupBy("states.{$nameColumn}")
                ->orderByDesc('count')
                ->get();

            $totalUsers = max($totalUsers, 1);
            $topGovernorate = $stateCounts->first()->state ?? 'N/A';
            $topStateCounts = $stateCounts->take(8);

            $othersCount = $stateCounts->skip(8)->sum('count');
            $othersPercentage = round(($othersCount / $totalUsers) * 100, 1);

       $distribution = $topStateCounts->map(function ($item) use ($totalUsers) {
         return [
            'governorate' => $item->state ?? 'Unknown',
            'count' => $item->count,
            'percentage' => round(($item->count / $totalUsers) * 100, 1),
        ];
      })->toArray();

    if ($othersCount > 0) {
     $distribution[] = [
        'governorate' => __("Others") . ' (' . $stateCounts->count() - 8 . ' ' . __('governorates') . ')',
        'count' => $othersCount,
        'percentage' => $othersPercentage,
     ];
    } 
    $months = collect(range(1, 12))->map(function ($m) {
    return date('M', mktime(0, 0, 0, $m, 1));
 });

    $programs = DB::table('cycles')->select('title')->pluck('title')->toArray();
    $programProgress = [];

    foreach ($programs as $program) {
         $programProgress[$program] = collect(range(1, 12))->map(function ($month) use ($program) {
         return rand(25, 100);
    });
    } 

      return response()->json([
                'summary' => [
                    'total_users' => $totalUsers,
                    'total_users_change' => $userChange,
                    'total_sessions' => $totalSessions,
                    'total_sessions_change' => $sessionsChange,
                    'sessions_duration' => round($totalDuration / 60, 1) . 'h',
                    'sessions_duration_change' => $durationChange,
                    'screening' => [
                        'first' => $screeningFirst,
                        'final' => $screeningFinal,
                        'total' => $screeningFirst + $screeningFinal,
                    ],
                ],
                'user_roles' => $roleCounts,
                'user_gender' => [
                    'male' => $male,
                    'female' => $female,
                    'total' => $totalGender,
                    'male_percentage' => $totalGender > 0 ? round(($male / $totalGender) * 100) : 0,
                    'female_percentage' => $totalGender > 0 ? round(($female / $totalGender) * 100) : 0,
                    'change' => $genderChange,
                ],
                'companies' => [
                    'total' => $totalCompanies,
                    'top_sectors' => $topSectors,
                    'development_stages' => $developmentStages,
                    'business_models' => $businessModels,
                    'target_markets' => $targetMarkets,
                    'state_distribution' => $states,
                    'country_distribution' => $countries,
                    'sdgs' => $sdgs,
                ],
                'user_demographics' => [
                    'total_users' => $totalUsers,
                    'total_governorates' => $stateCounts->count(),
                    'top_governorate' => $topGovernorate,
                    'distribution' => $distribution,
                    ],
               'programs_progress_over_time' => [
                'months' => $months,
                'progress' => $programProgress,
                ],
            ]);
        }
         

        $comingSessionsCount = DB::table('mentorship_requests')
            ->where('mentor_id', $user->id)
            ->whereNotNull('zoom_date')
            ->where('approved', 'yes')
            ->where('zoom_date', '>=', Carbon::now())
            ->count();
        
      $schedules = DB::table('schedules')
        ->where('user_id', $user->id)
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
