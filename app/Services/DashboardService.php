<?php

namespace App\Services;

use App\Step;
use App\User;
use App\Cycle;
use App\Service;
use App\Screening;
use Carbon\Carbon;
use App\Finalscreening;
use App\SurveyResponses;
use App\MentorshipRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardService
{
    protected $locale;
    protected $nameColumn;

    public function __construct()
    {
        $this->locale = app()->getLocale();
        $this->nameColumn = $this->locale === 'ar' ? 'name_ar' : 'name_en';
    }

    public function adminDashboard(string $nameColumn): array
    {
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

        $genderStats = DB::table('users')->selectRaw("gender, COUNT(*) as count")->groupBy('gender')->pluck('count', 'gender')->toArray();
        $male = $genderStats['Male'] ?? 0;
        $female = $genderStats['Female'] ?? 0;
        $totalGender = $male + $female;
        $genderChange = $totalGender > 0 ? round((($totalGender - 63) / 63) * 100, 1) : 0;


        $topSectors = DB::table('companies')->select('sector', DB::raw('COUNT(*) as count'))
            ->whereNotNull('sector')->groupBy('sector')->orderByDesc('count')->get();



        $states = DB::table('companies')->leftJoin('states', 'states.id', '=', 'companies.state_id')
            ->select("states.{$nameColumn} as state", DB::raw('COUNT(companies.id) as count'))
            ->groupBy("states.{$nameColumn}")->get();

        $countries = DB::table('companies')->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
            ->select("countries.{$nameColumn} as country", DB::raw('COUNT(companies.id) as count'))
            ->groupBy("countries.{$nameColumn}")
            ->get();
             
        $sdgCompanies = DB::table('companies')->select('Sustainable_development')
        ->whereNotNull('Sustainable_development')
        ->get();

         $sdgCounts = [];
         foreach ($sdgCompanies as $company) {
         $sdgArray = json_decode($company->Sustainable_development, true);
          if (is_array($sdgArray)) {
            foreach ($sdgArray as $sdgSlug) {
                $sdgCounts[$sdgSlug] = ($sdgCounts[$sdgSlug] ?? 0) + 1;
            }
            }
        }

       $sdgStartups = [
        'totalStartups' => array_sum($sdgCounts),
        'distribution' => collect($sdgCounts)->map(function ($count, $sdgSlug) {
            $key = str_replace(['sdg-', '-'], ['', '_'], $sdgSlug);
             return [
                'sdg' => __('admin.' . $key),
                'count' => $count,
            ];
            })->values(),
        ];
            

        $stateCounts = DB::table('users')->leftJoin('states', 'states.id', '=', 'users.state_id')
            ->select("states.{$nameColumn} as state", DB::raw('COUNT(users.id) as count'))
            ->whereNotNull('users.state_id')->groupBy("states.{$nameColumn}")->orderByDesc('count')->get();

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
                'governorate' => __("api.OtherGovernorates") . ' (' . $stateCounts->count() - 8 . ' ' . __('api.governorates') . ')',
                'count' => $othersCount,
                'percentage' => $othersPercentage,
            ];
        }

       $now = Carbon::now();
       $cycles = DB::table('cycles')->select('title', 'start', 'end')->get();

       $programsProgress = [
            'month' => $now->locale(app()->getLocale())->translatedFormat('F')
        ];

      foreach ($cycles as $cycle) {
        $start = Carbon::parse($cycle->start);
        $end = Carbon::parse($cycle->end);

        if ($end->lessThanOrEqualTo($start)) {
            $progress = 0;
        } elseif ($now->lessThan($start)) {
            $progress = 0;
        } elseif ($now->greaterThanOrEqualTo($end)) {
         $progress = 100;
         } else {
            $totalDays = $end->diffInDays($start);
            $elapsedDays = $now->diffInDays($start);
            $progress = round(($elapsedDays / $totalDays) * 100);
        }

        $monthLabel = $start->format('Y-m');
        $key = "{$cycle->title} ({$monthLabel})";

       $programsProgress[$key] = $progress;
    }
       $programStartups = DB::table('companies')->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->select('cycles.title', DB::raw('COUNT(companies.id) as count'))
        ->groupBy('cycles.title')->pluck('count', 'title');

       $sectorDistribution = DB::table('companies')
        ->join('sectors', 'companies.sector', '=', 'sectors.id')
        ->select("sectors.{$nameColumn} as sector", DB::raw('COUNT(companies.id) as count'))
        ->groupBy("sectors.{$nameColumn}")
        ->orderByDesc('count')
        ->get();

        $totalStartups = $sectorDistribution->sum('count');

        $topSectors = $sectorDistribution->take(10)->map(function ($item) use ($totalStartups) {
            return [
            'sector' => $item->sector,
            'count' => $item->count,
            'percentage' => $totalStartups > 0 ? round(($item->count / $totalStartups) * 100) : 0,
            ];
        });

        $sectorBreakdown = $sectorDistribution->take(10)->map(function ($item) use ($totalStartups) {
         return [
            'sector' => $item->sector,
            'count' => $item->count,
            'percentage' => $totalStartups > 0 ? round(($item->count / $totalStartups) * 100, 1) : 0,
            ];
        });

        $headquarters = DB::table('companies')->join('countries', 'companies.country_id', '=', 'countries.id')
            ->select("countries.{$nameColumn} as country", DB::raw('COUNT(companies.id) as count'))
            ->groupBy("countries.{$nameColumn}")->orderByDesc('count')->get();
        $hqTop = $headquarters->take(6);
        $hqOthers = $headquarters->skip(6)->sum('count');
        $hqDistribution = $hqTop->map(function ($item) {
            return ['country' => $item->country, 'count' => $item->count];
        })->toArray();
        if ($hqOthers > 0) {
            $hqDistribution[] = ['country' => __('api.OtherCountries'), 'count' => $hqOthers];
        }

       $targetMarketCompanies = DB::table('companies')
        ->select('targeted_market')
        ->whereNotNull('targeted_market')
        ->get();

        $countryCounts = [];

        foreach ($targetMarketCompanies as $company) {
         $ids = json_decode($company->targeted_market, true);
            if (is_array($ids)) {
            foreach ($ids as $id) {
            $countryCounts[$id] = ($countryCounts[$id] ?? 0) + 1;
            }
            }
        }

        $countryNames = DB::table('countries')
        ->whereIn('id', array_keys($countryCounts))
        ->pluck($nameColumn, 'id');

        $totalMarkets = array_sum($countryCounts);

        $targetMarketDistribution = collect($countryCounts)->map(function ($count, $id) use ($countryNames, $totalMarkets) {
            return [
            'market' => $countryNames[$id] ?? 'Unknown',
            'count' => $count,
            'percentage' => $totalMarkets > 0 ? round(($count / $totalMarkets) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values();

        $devStageStats = DB::table('companies')
        ->join('stages', 'companies.stage', '=', 'stages.id') 
        ->select("stages.{$nameColumn} as stage", DB::raw('COUNT(companies.id) as count'))
        ->whereNotNull('stage')
        ->groupBy("stages.{$nameColumn}")
        ->get();

        $totalDevStages = $devStageStats->sum('count');

        $devStagesDistribution = $devStageStats->map(function ($item) use ($totalDevStages) {
        return [
            'stage' => $item->stage,
            'count' => $item->count, 
            'percentage' => $totalDevStages > 0 ? round(($item->count / $totalDevStages) * 100, 1) : 0,
        ];
        });     

        $businessModelStats = DB::table('companies')->select('business_model', DB::raw('COUNT(*) as count'))
            ->whereNotNull('business_model')->groupBy('business_model')->orderByDesc('count')->get();
        $totalBizModels = $businessModelStats->sum('count');
        $businessModelDistribution = $businessModelStats->map(function ($item) {
            return ['model' => $item->business_model, 'count' => $item->count];
        });

        $completedSessions = DB::table('mentorship_requests')->whereNull('deleted_at')
        ->whereNotNull('start_url')
        ->whereDate('zoom_date', '<=', now()->toDateString())
        ->select('done')
        ->get();

        $totalSessionsAttended = $completedSessions->count();
        $totalSessionsMarked = $completedSessions->sum('done');
        $attendanceRate = $totalSessionsAttended > 0 ? round(($totalSessionsMarked / $totalSessionsAttended) * 100, 1) : 0;

        $totalTasks = DB::table('mentorship_tasks')->count();
        $completedTasks = DB::table('mentorship_tasks')->where('status', 'completed')->count();
        $completedTasksRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return [
            'totalUsers' => $totalUsers,
            'totalUsersChange' => $userChange,
            'totalSessions' => $totalSessions,
            'totalSessionsChange' => $sessionsChange,
            'sessionsDuration' => round($totalDuration / 60, 1) . 'h',
            'sessionsDurationChange' => $durationChange,
            'startupAttendanceRate' => [
                'rate' => $attendanceRate,
            ],
            'completedTasks' => [
                 'rate' => $completedTasksRate,
            ],

        'screening' => [
            'first' => $screeningFirst,
            'final' => $screeningFinal,
            'total' => $screeningFirst + $screeningFinal,
        ],
        'userRoles' => $roleCounts,
        'userGender' => [
            'male' => $male,
            'female' => $female,
            'total' => $totalGender,
            'malePercentage' => $totalGender > 0 ? round(($male / $totalGender) * 100) : 0,
            'femalePercentage' => $totalGender > 0 ? round(($female / $totalGender) * 100) : 0,
            'change' => $genderChange,
        ],
        'userDemographics' => [
            'totalUsers' => $totalUsers,
            'totalGovernorates' => $stateCounts->count(),
            'topGovernorate' => $topGovernorate,
            'distribution' => $distribution,
        ],
        'programsProgressOverTime' => $programsProgress,
        'programStartups' => $programStartups, 
        'topStartupSectors' => [
          'totalStartups' => $totalStartups,
          'distribution' => $topSectors,
        ],
        'sectorBreakdown' => [
          'distribution' => $sectorBreakdown,
        ],
        'startupHeadquarters' => [
          'distribution' => $hqDistribution,
        ],
        'targetMarketDistribution' => [
          'totalMarkets' => $totalMarkets,
          'distribution' => $targetMarketDistribution,
        ],
       'developmentStages' => [
         'totalStartups' => $totalDevStages,
         'distribution' => $devStagesDistribution,
       ],
       'businessModels' => [
         'totalStartups' => $totalBizModels,
         'distribution' => $businessModelDistribution,
      ],
       'countryDistribution' => $countries,
       'stateDistribution' => $states,
       'sdgStartups' => $sdgStartups,
       'metadata' => [
        'lastUpdated' => now()->format('Y-m-d H:i:s'),
       ],
   ];

}

    public function mentorDashboard(int $mentorId): array
    {
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
                'mr.start_url as meeting_link',
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
            ->select('mr.id as request_id', 'mt.id as task_id', 'mt.title as task_title', 'mt.status as task_status')
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

        $mentorAverageRating = DB::table('mentorship_requests')
            ->where('mentor_id', $mentorId)
            ->whereNotNull('rating')
            ->avg('rating');
        $mentorRatingAverage = $mentorAverageRating ? round($mentorAverageRating, 2) : null;

        $totalHours = DB::table('mentorship_requests')
            ->where('mentor_id', $mentorId)
            ->whereNotNull('period')
            ->sum('period'); 
        $totalHoursFormatted = round($totalHours / 60, 2);

        $totalSessions = DB::table('mentorship_requests')
        ->where('mentor_id', $mentorId)
        ->whereNotNull('zoom_date')
        ->count();

        $sessionsSummary = DB::table('mentorship_requests')
        ->selectRaw("MONTH(zoom_date) as month, COUNT(*) as sessions, SUM(period) as total_minutes")
        ->where('mentor_id', $mentorId)
        ->whereNotNull('zoom_date')
        ->whereYear('zoom_date', now()->year)
        ->groupBy(DB::raw('MONTH(zoom_date)'))
        ->orderBy(DB::raw('MONTH(zoom_date)'))
        ->get()
        ->map(function ($item) {
            return [
                'month' => Carbon::create()->month($item->month)->format('M'),
                'sessions' => (int) $item->sessions,
                'hours' => round($item->total_minutes / 60, 2),
            ];
        });


        return [
            'comingSessionsCount' => $comingSessionsCount,
            'currentSchedule' => $schedules,
            'nextSessions' => $nextSessions,
            'completedTasks' => $completedTasks,
            'nonCompletedTasks' => $nonCompletedTasks,
            'sessionWithoutTasks' => $sessionWithoutTasks,
            'startupAttendanceRate' => $attendsRate,
            'mentorAverageRating' => $mentorRatingAverage,
            'totalMentorshipHours' => $totalHoursFormatted,
            'totalMentorshipSessions' => $totalSessions,
            'sessionsSummary' => $sessionsSummary,
        ];
    }

   public function organizationManagerDashboard(int $organizationId, string $nameColumn): array
{
    $usersQuery = DB::table('users')->where('organization_id',$organizationId);
    $totalUsers = $usersQuery->count();
    $prevTotalUsers = (clone $usersQuery)->whereDate('created_at', '<', Carbon::now()->subMonth())->count();
    $userChange = $prevTotalUsers > 0 ? round(($totalUsers - $prevTotalUsers) / $prevTotalUsers * 100, 1) : 0;

    $sessionsQuery = DB::table('training_sessions')
        ->join('steps', 'training_sessions.step_id', '=', 'steps.id')
        ->join('cycles', 'steps.cycle_id', '=', 'cycles.id')
        ->where('cycles.organization_id', $organizationId);

    $totalSessions = $sessionsQuery->count();
    $prevTotalSessions = (clone $sessionsQuery)->whereDate('training_sessions.created_at', '<', Carbon::now()->subMonth())->count();
    $totalDuration = $sessionsQuery->sum('training_sessions.duration');
    $prevDuration = (clone $sessionsQuery)->whereDate('training_sessions.created_at', '<', Carbon::now()->subMonth())->sum('training_sessions.duration');

    $sessionsChange = $prevTotalSessions > 0 ? round(($totalSessions - $prevTotalSessions) / $prevTotalSessions * 100, 1) : 0;
    $durationChange = $prevDuration > 0 ? round(($totalDuration - $prevDuration) / $prevDuration * 100, 1) : 0;

    $screeningFirst = DB::table('screenings')
        ->join('users', 'screenings.user_id', '=', 'users.id')
        ->where('users.organization_id', $organizationId)->count();

    $screeningFinal = DB::table('finalscreenings')
        ->join('steps', 'finalscreenings.step_id', '=', 'steps.id')
        ->join('cycles', 'steps.cycle_id', '=', 'cycles.id')
        ->where('cycles.organization_id',$organizationId)->count();

    $roles = DB::table('roles')->pluck('name', 'id')->toArray();
    $roleCounts = [];
    foreach (['Registered', 'Mentor', 'Judger', 'organizationManager'] as $key) {
        $roleId = array_search($key, $roles);
        $count = DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('users.organization_id', $organizationId)
            ->where('model_has_roles.role_id', $roleId)->count();
        $roleCounts[strtolower(str_replace(' ', '', $key)) . 's'] = $count;
    }

    $genderStats = DB::table('users')
        ->where('organization_id', $organizationId)
        ->selectRaw("gender, COUNT(*) as count")
        ->groupBy('gender')
        ->pluck('count', 'gender')->toArray();
    $male = $genderStats['Male'] ?? 0;
    $female = $genderStats['Female'] ?? 0;
    $totalGender = $male + $female;
    $genderChange = $totalGender > 0 ? round(($totalGender - 63) / 63 * 100, 1) : 0;

    $stateCounts = DB::table('users')
        ->where('organization_id', $organizationId)
        ->leftJoin('states', 'states.id', '=', 'users.state_id')
        ->whereNotNull('users.state_id')
        ->select("states.{$nameColumn} as state", DB::raw('COUNT(users.id) as count'))
        ->groupBy("states.{$nameColumn}")
        ->orderByDesc('count')->get();

    $topGovernorate = $stateCounts->first()->state ?? 'N/A';
    $topStateCounts = $stateCounts->take(8);
    $othersCount = $stateCounts->skip(8)->sum('count');
    $othersPercentage = round(($othersCount / max($totalUsers, 1)) * 100, 1);
    $distribution = $topStateCounts->map(fn($item) => [
        'governorate' => $item->state ?? 'Unknown',
        'count' => $item->count,
        'percentage' => round(($item->count / max($totalUsers, 1)) * 100, 1),
    ])->toArray();

    if ($othersCount > 0) {
        $distribution[] = [
            'governorate' => __("api.OtherGovernorates") . ' (' . $stateCounts->count() - 8 . ' ' . __('api.governorates') . ')',
            'count' => $othersCount,
            'percentage' => $othersPercentage,
        ];
    }

    $now = Carbon::now();
    $locale = app()->getLocale();

    $programsProgress = [
        'month' => $now->locale($locale)->translatedFormat('F')
    ];

    $cycles = DB::table('cycles')
    ->where('organization_id', $organizationId)
    ->select('title', 'start', 'end')
    ->get();

    foreach ($cycles as $cycle) {
        $start = Carbon::parse($cycle->start);
        $end = Carbon::parse($cycle->end);

    if ($end->lessThanOrEqualTo($start)) {
        $progress = 0;
    } elseif ($now->lessThan($start)) {
        $progress = 0;
    } elseif ($now->greaterThanOrEqualTo($end)) {
        $progress = 100;
    } else {
        $totalDays = $end->diffInDays($start);
        $elapsedDays = $now->diffInDays($start);
        $progress = round(($elapsedDays / $totalDays) * 100);
    }

    $monthLabel = $start->format('Y-m');
    $key = "{$cycle->title} ({$monthLabel})";

    $programsProgress[$key] = $progress;
}
    $programStartups = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->where('cycles.organization_id', $organizationId)
        ->select('cycles.title', DB::raw('COUNT(companies.id) as count'))
        ->groupBy('cycles.title')->pluck('count', 'title');

    $sectorDistribution = DB::table('companies')
    ->join('cycles', 'companies.cycle', '=', 'cycles.id')
    ->join('sectors', 'companies.sector', '=', 'sectors.id')
    ->where('cycles.organization_id', $organizationId)
    ->select("sectors.{$nameColumn} as sector", DB::raw('COUNT(companies.id) as count'))
    ->groupBy("sectors.{$nameColumn}")
    ->orderByDesc('count')
    ->get();

   $totalStartups = $sectorDistribution->sum('count');

   $topSectors = $sectorDistribution->take(10)->map(fn($item) => [
    'sector' => $item->sector,
    'count' => $item->count,
    'percentage' => $totalStartups > 0 ? round(($item->count / $totalStartups) * 100) : 0,
  ]);

  $sectorBreakdown = $sectorDistribution->take(10)->map(fn($item) => [
    'sector' => $item->sector,
    'count' => $item->count,
    'percentage' => $totalStartups > 0 ? round(($item->count / $totalStartups) * 100, 1) : 0,
  ]);

    $states = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->leftJoin('states', 'states.id', '=', 'companies.state_id')
        ->where('cycles.organization_id', $organizationId)
        ->select("states.{$nameColumn} as state", DB::raw('COUNT(companies.id) as count'))
        ->groupBy("states.{$nameColumn}")->get();

    $countries = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->leftJoin('countries', 'countries.id', '=', 'companies.country_id')
        ->where('cycles.organization_id', $organizationId)
        ->select("countries.{$nameColumn} as country", DB::raw('COUNT(companies.id) as count'))
        ->groupBy("countries.{$nameColumn}")->get();
       
       
   $targetMarketCompanies = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->where('cycles.organization_id', $organizationId)
        ->select('companies.targeted_market')
        ->whereNotNull('companies.targeted_market')
        ->get();

    $countryCounts = [];

    foreach ($targetMarketCompanies as $company) {
        $ids = json_decode($company->targeted_market, true);
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $countryCounts[$id] = ($countryCounts[$id] ?? 0) + 1;
            }
        }
    }

    $countryNames = DB::table('countries')
        ->whereIn('id', array_keys($countryCounts))
        ->pluck($nameColumn, 'id');

    $totalMarkets = array_sum($countryCounts);
    $targetMarketDistribution = collect($countryCounts)->map(function ($count, $id) use ($countryNames, $totalMarkets) {
        return [
            'market' => $countryNames[$id] ?? 'Unknown',
            'count' => $count,
            'percentage' => $totalMarkets > 0 ? round(($count / $totalMarkets) * 100, 1) : 0,
        ];
    })->values();

    $devStageStats = DB::table('companies')
    ->join('cycles', 'companies.cycle', '=', 'cycles.id')
    ->join('stages', 'companies.stage', '=', 'stages.id')
    ->where('cycles.organization_id', $organizationId)
    ->whereNotNull('companies.stage')
    ->select("stages.{$nameColumn} as stage", DB::raw('COUNT(companies.id) as count'))
    ->groupBy("stages.{$nameColumn}")
    ->get();

    $totalDevStages = $devStageStats->sum('count');

    $devStagesDistribution = $devStageStats->map(function ($item) use ($totalDevStages) {
     return [
        'stage' => $item->stage,
        'count' => $item->count,
        'percentage' => $totalDevStages > 0 ? round(($item->count / $totalDevStages) * 100, 1) : 0,
     ];
   });

  
    $businessModelDistribution = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->where('cycles.organization_id', $organizationId)
        ->select('companies.business_model', DB::raw('COUNT(*) as count'))
        ->groupBy('companies.business_model')->get();
    $totalBizModels = $businessModelDistribution->sum('count');

    $sdgCompanies = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->where('cycles.organization_id', $organizationId)
        ->select('Sustainable_development')->whereNotNull('Sustainable_development')->get();
    $sdgCounts = [];
    foreach ($sdgCompanies as $company) {
        $sdgArray = json_decode($company->Sustainable_development, true);
        if (is_array($sdgArray)) {
            foreach ($sdgArray as $sdgSlug) {
                $sdgCounts[$sdgSlug] = ($sdgCounts[$sdgSlug] ?? 0) + 1;
            }
        }
    }
     $sdgStartups = [
        'totalStartups' => array_sum($sdgCounts),
        'distribution' => collect($sdgCounts)->map(function ($count, $sdgSlug) {
            $key = str_replace(['sdg-', '-'], ['', '_'], $sdgSlug);
             return [
                'sdg' => __('admin.' . $key),
                'count' => $count,
            ];
            })->values(),
      ];

    $completedSessions = DB::table('mentorship_requests')
        ->join('users', 'mentorship_requests.user_id', '=', 'users.id')
        ->where('users.organization_id', $organizationId)
        ->whereNull('mentorship_requests.deleted_at')
        ->whereNotNull('start_url')
        ->whereDate('zoom_date', '<=', now()->toDateString())
        ->select('done')->get();

    $totalSessionsAttended = $completedSessions->count();
    $totalSessionsMarked = $completedSessions->sum('done');
    $attendanceRate = $totalSessionsAttended > 0 ? round(($totalSessionsMarked / $totalSessionsAttended) * 100, 1) : 0;

    $totalTasks = DB::table('mentorship_tasks')
        ->join('users', 'mentorship_tasks.user_id', '=', 'users.id')
        ->where('users.organization_id', $organizationId)->count();
    $completedTasks = DB::table('mentorship_tasks')
        ->join('users', 'mentorship_tasks.user_id', '=', 'users.id')
        ->where('users.organization_id', $organizationId)
        ->where('mentorship_tasks.status', 'completed')->count();
    $completedTasksRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

    $headquarters = DB::table('companies')
        ->join('cycles', 'companies.cycle', '=', 'cycles.id')
        ->join('countries', 'companies.country_id', '=', 'countries.id')
        ->where('cycles.organization_id', $organizationId)
        ->select("countries.{$nameColumn} as country", DB::raw('COUNT(companies.id) as count'))
        ->groupBy("countries.{$nameColumn}")
        ->orderByDesc('count')
        ->get();

    $hqTop = $headquarters->take(6);
    $hqOthers = $headquarters->skip(6)->sum('count');

    $hqDistribution = $hqTop->map(function ($item) {
        return [
            'country' => $item->country,
            'count' => $item->count,
        ];
    })->toArray();

    if ($hqOthers > 0) {
        $hqDistribution[] = [
            'country' => __('api.OtherCountries'),
            'count' => $hqOthers,
        ];
    }

    return [
        'totalUsers' => $totalUsers,
        'totalUsersChange' => $userChange,
        'totalSessions' => $totalSessions,
        'totalSessionsChange' => $sessionsChange,
        'sessionsDuration' => round($totalDuration / 60, 1) . 'h',
        'sessionsDurationChange' => $durationChange,
        'startupAttendanceRate' => ['rate' => $attendanceRate],
        'completedTasks' => ['rate' => $completedTasksRate],
        'screening' => [
            'first' => $screeningFirst,
            'final' => $screeningFinal,
            'total' => $screeningFirst + $screeningFinal,
        ],
        'userRoles' => $roleCounts,
        'userGender' => [
            'male' => $male,
            'female' => $female,
            'total' => $totalGender,
            'malePercentage' => $totalGender > 0 ? round(($male / $totalGender) * 100) : 0,
            'femalePercentage' => $totalGender > 0 ? round(($female / $totalGender) * 100) : 0,
            'change' => $genderChange,
        ],
        'userDemographics' => [
            'totalUsers' => $totalUsers,
            'totalGovernorates' => $stateCounts->count(),
            'topGovernorate' => $topGovernorate,
            'distribution' => $distribution,
        ],
        'programsProgressOverTime' => $programsProgress,
        'programStartups' => $programStartups,
        'topStartupSectors' => [
            'totalStartups' => $totalStartups,
            'distribution' => $topSectors,
        ],
        'sectorBreakdown' => ['distribution' => $sectorBreakdown],
        'startupHeadquarters' => [
           'distribution' => $hqDistribution,
        ],
        'targetMarketDistribution' => [
            'totalMarkets' => $totalMarkets,
            'distribution' => $targetMarketDistribution,
        ],
        'developmentStages' => [
            'totalStartups' => $totalDevStages,
            'distribution' => $devStagesDistribution,
        ],
        'businessModels' => [
            'totalStartups' => $totalBizModels,
            'distribution' => $businessModelDistribution,
        ],
        'countryDistribution' => $countries,
        'stateDistribution' => $states,
        'sdgStartups' => $sdgStartups,
        'metadata' => ['lastUpdated' => now()->format('Y-m-d H:i:s')],
    ];
}
public function judgerDashboard(): array
{
    $judgerId = auth()->id();

    $screeningsCount = Screening::where('user_id', $judgerId)->count();
    $finalScreeningsCount = FinalScreening::whereJsonContains('judges', (string) $judgerId)->count();

    $stepIds = Screening::where('user_id', $judgerId)->pluck('step_id')->unique();

    if ($stepIds->isEmpty()) {
        return [
            'screeningsCount' => $screeningsCount,
            'finalScreeningsCount' => $finalScreeningsCount,
            'judgingList' => [],
        ];
    }

    $getNotEvaluatedCompanies = collect(DB::select("
        SELECT
            results.id AS result_id,
            results.evaluation_id,
            eval.step_id AS step_id,
            results.company_id AS company_id,
            results.status
        FROM
            screening_evaluation_results as results
        LEFT JOIN
            screening_evaluation as eval ON eval.id = results.evaluation_id
        WHERE
            results.user_id = ?
    ", [ $judgerId ]));

    $establishedEvaluations = collect(DB::select("
        SELECT
            eval.id,
            eval.step_id,
            eval.status
        FROM
            screening_evaluation as eval
        WHERE
            eval.step_id IN (" . implode(',', $stepIds->toArray()) . ")
    "));

    $steps = Step::whereIn('id', $stepIds)
        ->with([
            'cycle:id,title,start,end',
            'companies' => function ($q) {
                $q->select('id', 'name_en','logo','step', 'cycle')
                    ->with(['user:id,first_name_en,last_name_en,title_en,company_id,img_url']);
            }
        ])
        ->get(['id', 'title', 'description', 'from', 'to', 'cycle_id']);

    $judgingList = $steps->map(function ($step) use ($getNotEvaluatedCompanies, $establishedEvaluations) {
        $stepEval = $establishedEvaluations->firstWhere('step_id', $step->id);
        $isEvalEstablished = $stepEval && $stepEval->status != 0;

        $companies = $step->companies->map(function ($company) use ($getNotEvaluatedCompanies, $isEvalEstablished, $step) {
            $user = $company->user;
            $userImg = $user?->img_url
                ? Storage::disk('public_uploads')->url($user->img_url)
                : null;

            if (!$isEvalEstablished) {
                $evaluationStatus = 'not_started';
            } else {
                $responds = $getNotEvaluatedCompanies->where('company_id', $company->id)
                    ->where('step_id', $step->id);

                if ($responds->isEmpty()) {
                    $evaluationStatus = 'not_evaluated';
                } else {
                    $havePending = $responds->where('status', 0)->count();
                    $evaluationStatus = $havePending > 0 ? 'in_progress' : 'evaluated';
                }
            }

            return [
                'startupLogo' => $company->logo_url,
                'startupName'       => $company->name_en,
                'imgUrl'           => $userImg,
                'userName'          => optional($user)->first_name_en . ' ' . optional($user)->last_name_en,
                'userTitle'         => optional($user)->title_en,
                'evaluationStatus'  => $evaluationStatus,
            ];
        });

        return [
            'stepTitle'     => $step->title,
            'stepFrom'      => $step->from ? Carbon::parse($step->from)->format('Y-m-d') : null,
            'stepTo'        => $step->to ? Carbon::parse($step->to)->format('Y-m-d') : null,
            'stepDesc'      => $step->description,
            'cycleTitle'    => optional($step->cycle)->title,
            'cycleStart'    => optional($step->cycle)?->start ? Carbon::parse($step->cycle->start)->format('Y-m-d') : null,
            'cycleEnd'      => optional($step->cycle)?->end ? Carbon::parse($step->cycle->end)->format('Y-m-d') : null,
            'companies'     => $companies->toArray(),
        ];
    });

    return [
        'screening'      => $screeningsCount,
        'finalScreening' => $finalScreeningsCount,
        'judgingList'    => $judgingList->toArray(),
    ];
}
public function startupDashboard($userId)
{
    $user = User::with('company')->find($userId);
    $company = $user->company;

    $cycle = Cycle::find($company->cycle);
    $currentStep = Step::find($company->step);
    $totalSteps = Step::where('cycle_id', $cycle->id)->count();

    $stepData = Step::where('id', $company->step)->first();
    $locale = app()->getLocale();
    $l = $locale;

    $steps = Step::where('cycle_id', $cycle->id)
        ->orderBy('arr')
        ->get()
        ->map(function ($step) use ($l,$stepData) {
            $to = Carbon::parse($step->to)->format('d-m-Y');

            $status = $l === 'ar' ? 'لم تبدأ' : 'Not started';
            $completedDate = null;

            if ($stepData) {
                if ($stepData->arr > $step->arr) {
                    $status = $l === 'ar' ? 'انتهت' : 'Completed';
                    $completedDate = $to;
                } elseif ($stepData->arr == $step->arr) {
                    $status = $l === 'ar' ? 'بدأت' : 'Started';
                }
            }

            return [
                'id' => $step->id,
                'name' => $step->title,
                'description' => $step->description,
                'dueDate' => $to,
                'status' => $status,
                'completedDate' => $completedDate,
            ];
        });

    $progress = '0%';
    if ($cycle->start && $cycle->end) {
        $start = Carbon::parse($cycle->start);
        $end = Carbon::parse($cycle->end);
        $today = Carbon::today();

        $totalDays = $start->diffInDays($end);
        $passedDays = $start->diffInDays(min($today, $end));
        $progress = $totalDays > 0 ? round(($passedDays / $totalDays) * 100, 2) . '%' : '0%';
    }

    $mentorIds = DB::table('cycles_mentors')
        ->where('cycle_id', $cycle->id)
        ->pluck('user_id');

    $allMentorPrograms = DB::table('cycles_mentors')
        ->whereIn('user_id', $mentorIds)
        ->get()
        ->groupBy('user_id');

    $mentors = User::whereIn('id', $mentorIds)
        ->with([
            'bio:id,user_id,bio_en,bio_ar,linkedin,specialization_en,specialization_ar',
            'mentorExpertises:id,title,title_ar',
            'roles:name'
        ])
        ->get()
        ->map(function ($mentor) use ($allMentorPrograms, $locale) {
            $rating = MentorshipRequest::where('mentor_id', $mentor->id)
                ->whereNotNull('rating')
                ->avg('rating');

            $programs = $allMentorPrograms[$mentor->id] ?? collect();
            $imgUrl = $mentor?->img_url 
                ? Storage::disk('public_uploads')->url($mentor->img_url)
                : null;

            return [
                'id' => $mentor->id,
                'name' => $mentor->name,
                'email' => $mentor->email,
                'title' => $mentor->title_en,
                'imgUrl' => $imgUrl,
                'bio' => optional($mentor->bio)->{"bio_$locale"},
                'linkedin' => optional($mentor->bio)->linkedin,
                'specialization' => optional($mentor->bio)->{"specialization_" . $locale},
                'mentorExpertises' => $mentor->mentorExpertises->map(fn ($exp) => $exp->{"title" . ($locale === 'ar' ? '_ar' : '')}),
                'rating' => round($rating ?? 0, 2),
                'programCount' => $programs->pluck('cycle_id')->unique()->count(),
                'programIds' => $programs->pluck('cycle_id')->unique()->values(),
                'role' => $mentor->getRoleNames()->first(),
            ];
        });

        
    $availableSurveys = SurveyResponses::where('user_id', $user->id)
    ->where('cycle_id', $cycle->id)
    ->whereNull('responses')
    ->with([
        'mentorship_session.mentor',
        'mentorship_step',
        'training_session',
        'training_step',
        'training_quiz',
    ])
    ->get();

    $availableSurveys = $availableSurveys->map(function ($item) {
     $response = [
        'id' => $item->id,
        'triggeredOn' => $item->triggered_on,
        'triggeredBy' => $item->triggered_by,
     ];

    if ($item->triggered_by === 'due') {
        if ($item->triggered_on === 'mentorship') {
            $response['title'] = $item->mentorship_step?->title ?? 'N/A';
        } elseif ($item->triggered_on === 'training') {
            $response['title'] = $item->training_step?->title ?? 'N/A';
        }
    }

    if ($item->triggered_by === 'session') {
        if ($item->triggered_on === 'mentorship') {
            $mentor = $item->mentorship_session?->mentor;
            $response['mentorName'] = $mentor
                ? trim($mentor->first_name_en . ' ' . $mentor->last_name_en)
                : null;
        } elseif ($item->triggered_on === 'training') {
            $response['mentorName'] = null; 
        }
    }

    if ($item->triggered_by === 'quiz' && $item->triggered_on === 'training') {
        $response['title'] = $item->training_quiz?->title ?? 'N/A';
    }

    return $response;
});


    $nextMentorshipSessions = DB::table('mentorship_requests as mr')
    ->join('users as m', 'm.id', '=', 'mr.mentor_id')
    ->where('mr.user_id', $userId)
    ->whereNotNull('mr.zoom_date')
    ->where('mr.zoom_date', '>=', Carbon::now())
    ->orderBy('mr.zoom_date', 'asc')
    ->select(
        'mr.zoom_date as zoom_date',
        'mr.period',
        'mr.mentor_id',
        'mr.admin_comment as admin_notes',
        'mr.join_url as meeting_link',
        DB::raw("CONCAT(m.first_name_en, ' ', m.last_name_en) as mentor_name"),
        'm.img_url as mentor_img'
    )
    ->get()
    ->map(function ($session) {
        $zoomDate = Carbon::parse($session->zoom_date);

        return [
            'mentorId' => $session->mentor_id,
            'mentorName' => $session->mentor_name,
            'imgUrl' => $session->mentor_img 
                ? Storage::disk('public_uploads')->url($session->mentor_img) 
                : null,
            'date' => $zoomDate->format('Y-m-d'),
            'time' => $zoomDate->format('H:i'),
            'duration' => $session->period,
            'adminNotes' => $session->admin_notes,
            'meetingLink' => $session->meeting_link,
        ];
    });

    $latestServices = Service::whereHas('organizations')
    ->with([
        'serviceCategory:id,name_en,name_ar',
    ])
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get()
    ->map(function ($service) use ($locale) {
        return [
            'serviceId' => $service->id,
            'serviceName' => $locale === 'ar' ? $service->name_ar : $service->name_en,
            'serviceType' => $service->serviceCategory
                ? ($locale === 'ar' ? $service->serviceCategory->name_ar : $service->serviceCategory->name_en)
                : null,
        ];
    });

    $services = Service::whereHas('organizations')->count();
    $mentorCount = $mentorIds->unique()->count();
    $completedTasks = DB::table('mentorship_tasks')->where('status', 'completed')->count();

    $mentorSchedules = DB::table('schedules as s')
    ->join('users as u', 'u.id', '=', 's.user_id')
    ->whereIn('s.user_id', $mentorIds)
    ->select(
        's.id',
        's.user_id as mentor_id',
        DB::raw("CONCAT(u.first_name_en, ' ', u.last_name_en) as mentor_name"),
        'u.img_url as mentor_img',
        's.type',
        's.days',
        's.tfrom',
        's.tto',
        's.notes',
        's.active'
    )
    ->get()
    ->map(function ($schedule) {
        return [
            'id' => $schedule->id,
            'mentorId' => $schedule->mentor_id,
            'mentorName' => $schedule->mentor_name,
            'imgUrl' => $schedule->mentor_img 
                ? Storage::disk('public_uploads')->url($schedule->mentor_img)
                : null,
            'type' => $schedule->type,
            'days' => $schedule->days,
            'start' => $schedule->tfrom,
            'end' => $schedule->tto,
            'notes' => $schedule->notes,
            'availability' => (bool) $schedule->active,
        ];
    });

     $latestEvents = DB::table('calendars')
        ->select('id', 'title', 'start','allDay')
        ->whereDate('start', '>=', now()->format('Y-m-d'))
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderBy('start', 'asc')
        ->limit(5)
        ->get()
        ->map(function ($event) {
            $start = Carbon::parse($event->start);
            $time = $start->format('H:i:s');

            return [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $start->format('Y-m-d'),
                'time' => $time,
                'allDay' => $event->allDay ? true : false,
            ];
        });

        $tasks = DB::table('mentorship_requests as mr')
            ->leftJoin('mentorship_tasks as mt', 'mt.mentorship_request_id', '=', 'mr.id')
            ->where('mr.user_id', $user->id)
            ->select('mr.id as request_id', 'mt.id as task_id', 'mt.title as task_title', 'mt.status as task_status')
            ->get();

        $completedTasks = $tasks->where('task_status', 'completed')->values()->count();

        $completedSessions = DB::table('mentorship_requests')
            ->where('user_id', $user->id)
            ->whereNotNull('start_url')
            ->whereDate('zoom_date', '<=', now()->toDateString())
            ->select('done')
            ->get();

        $total = $completedSessions->count();
        $totalMentorshipMinutes = DB::table('mentorship_requests')
        ->where('user_id', $user->id)
        ->whereNotNull('start_url') 
        ->whereDate('zoom_date', '<=', now()->toDateString())
        ->sum('period');

     $mentorshipHours = round($totalMentorshipMinutes / 60, 2);

    return [
        'program' => [
            'id' => $cycle->id,
            'name' => $cycle->title,
            'description' => $cycle->description,
            'startDate' => $cycle->start->format('Y-m-d'),
            'endDate' => $cycle->end->format('Y-m-d'),
            'currentStep' => $currentStep->id,
            'totalSteps' => $totalSteps,
            'progress' => $progress,
        ],
        'programSteps' => $steps,
        'mentors' => $mentors,
        'availableSurveys' => $availableSurveys,
        'mentorshipSessions' => $nextMentorshipSessions,
        'latestServices' => $latestServices,
        'services' => $services,
        'mentorCount' => $mentorCount,
        'programProgress' => $progress,
        'completedTasks' => $completedTasks,
        'mentorAvailabilitySlots' => $mentorSchedules,
        'latestEvents' => $latestEvents,
        'analytics' => [
            'programProgress' => $progress,
            'tasksCompleted' => $completedTasks,
            'totalTasks' => $total,
            'mentorshipHours' => $mentorshipHours,
        ]
    ];
}

}
