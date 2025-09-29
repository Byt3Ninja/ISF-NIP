<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MentorshipReportService
{
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['program']) && $filters['program'] !== 'all') {
            $query->where('cycles.title', $filters['program']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('mentorship_requests.zoom_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('mentorship_requests.zoom_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getSummary(array $filters)
    {
    $query = DB::table('mentorship_requests')
        ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id');

    $this->applyFilters($query, $filters);

    $totalMentors = DB::table('mentorship_requests')->distinct('mentor_id')->count('mentor_id');
    $activeMentors = DB::table('mentorship_requests')->where('done', 1)->distinct('mentor_id')->count('mentor_id');

    $requests = (clone $query)->count();
    $approved = (clone $query)->where('approved', 'yes')->count();
    $completed = (clone $query)->where('done', 1)->count();
    $totalMinutes = (clone $query)->whereNotNull('period')->sum('period');
    $totalHours = $totalMinutes / 60;

    $avgDuration = (clone $query)->whereNotNull('period')->avg('period');
    $avgAttendance = $approved > 0 ? round(($completed / $approved) * 100, 1) : 0;
    $completionRate = $approved > 0 ? round(($completed / $approved) * 100, 1) : 0;

    return [
        'totalMentors' => $totalMentors,
        'activeMentors' => $activeMentors,
        'requests' => $requests,
        'approvedSessions' => $approved,
        'completedSessions' => $completed,
        'totalHours' => round($totalHours, 1),
        'completionRate' => $completionRate,
        'avgDuration' => round($avgDuration, 1),
        'avgAttendance' => $avgAttendance
    ];
    }

    public function getWeeklyTrends(array $filters)
    {
        $weeks = $filters['weeks'] ?? 8;

        $query = DB::table('mentorship_requests')
            ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
            ->selectRaw("
                YEARWEEK(zoom_date, 1) as week,
                COUNT(*) as total_requests,
                SUM(CASE WHEN approved = 'yes' THEN 1 ELSE 0 END) as total_approvals
            ");

        $this->applyFilters($query, $filters);

        $data = $query->groupBy('week')
            ->orderBy('week', 'desc')
            ->limit($weeks)
            ->get()
            ->reverse()
            ->values();

        $labels = [];
        $requests = [];
        $approvals = [];

        foreach ($data as $row) {
            $labels[] = 'W' . substr($row->week, -2);
            $requests[] = $row->total_requests;
            $approvals[] = $row->total_approvals;
        }

        return [
            'labels' => $labels,
            'requests' => $requests,
            'approvals' => $approvals,
        ];
    }

    public function getMentorHours(array $filters)
    {
    $query = DB::table('mentorship_requests')
        ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
        ->select('mentor_id', DB::raw('SUM(period) as total_minutes'))
        ->groupBy('mentor_id');

    $this->applyFilters($query, $filters);

    $results = $query->get();

    $mentorData = DB::table('users')
        ->whereIn('id', $results->pluck('mentor_id'))
        ->select('id', 'first_name_en', 'last_name_en')
        ->get()
        ->keyBy('id');

    return $results->map(function ($item) use ($mentorData) {
        $mentor = $mentorData[$item->mentor_id] ?? null;

        return [
            'id' => $item->mentor_id,
            'name' => $mentor ? trim("{$mentor->first_name_en} {$mentor->last_name_en}") : 'Unknown',
            'hours' => round($item->total_minutes / 60, 2)
        ];
    })->values();
    }

    public function getMentorPerformance(array $filters)
    {
    $query = DB::table('mentorship_requests')
        ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
        ->select(
            'mentor_id',
            DB::raw('GROUP_CONCAT(DISTINCT cycles.title ORDER BY cycles.title ASC SEPARATOR ", ") as programs'),
            DB::raw('COUNT(*) as requests'),
            DB::raw("SUM(CASE WHEN approved = 'yes' THEN 1 ELSE 0 END) as approved"),
            DB::raw("SUM(CASE WHEN done = 1 THEN 1 ELSE 0 END) as completed"),
            DB::raw('SUM(period) as total_minutes'),
            DB::raw('AVG(period) as avg_duration'),
            DB::raw('AVG(rating) as rating')
        )
        ->groupBy('mentor_id');

    $this->applyFilters($query, $filters);

    $data = $query->get();

    $mentors = DB::table('users')
        ->whereIn('id', $data->pluck('mentor_id'))
        ->select('id', 'first_name_en', 'last_name_en')
        ->get()
        ->keyBy('id');

    return $data->map(function ($item) use ($mentors) {
        $mentor = $mentors[$item->mentor_id] ?? null;

        $name = $mentor
            ? trim("{$mentor->first_name_en} {$mentor->last_name_en}")
            : 'Unknown';

        return [
            'id' => $item->mentor_id,
            'name' => $name,
            'programs' => $item->programs ?? 'N/A',
            'requests' => (int) $item->requests,
            'approved' => (int) $item->approved,
            'completed' => (int) $item->completed,
            'hours' => round($item->total_minutes / 60, 2),
            'attendance' => $item->approved > 0
                ? round(($item->completed / $item->approved) * 100, 1)
                : 0,
            'rating' => round($item->rating ?? 0, 1),
            'completion_rate' => $item->approved > 0
                ? round(($item->completed / $item->approved) * 100, 1)
                : 0
        ];
    })->values();
    }

    public function getStartupEngagement(array $filters)
    {
       $authUserId = Auth::id();
 
       $query = DB::table('mentorship_requests')
        ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
        ->where('user_id', '<>', $authUserId)
        ->select(
            'user_id',
            DB::raw('COUNT(*) as requests'),
            DB::raw("SUM(CASE WHEN done = 1 THEN 1 ELSE 0 END) as completed"),
            DB::raw('SUM(period) as total_minutes'),
            DB::raw('AVG(rating) as rating'),
            DB::raw('GROUP_CONCAT(DISTINCT cycles.title ORDER BY cycles.title ASC SEPARATOR ", ") as programs')
        )
        ->groupBy('user_id');

        $this->applyFilters($query, $filters);

        $data = $query->get();

        $startups = DB::table('users')
        ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
        ->whereIn('users.id', $data->pluck('user_id'))
        ->select('users.id', 'users.first_name_en', 'users.last_name_en', 'companies.name_en as company_name')
        ->get()
        ->keyBy('id');

        return $data->map(function ($item) use ($startups) {
            $startup = $startups[$item->user_id] ?? null;

            $name = $startup
            ? trim("{$startup->first_name_en} {$startup->last_name_en}")
            : 'Unknown';

         $companyName = $startup->company_name ?? 'N/A';

         $attendance = $item->requests > 0
            ? round(($item->completed / $item->requests) * 100, 1)
            : 0;

         return [
            'id' => $item->user_id,
            'name' => $name,
            'company' => $companyName,
            'programs' => $item->programs ?? 'N/A',
            'requests' => (int) $item->requests,
            'completed' => (int) $item->completed,
            'hours' => round($item->total_minutes / 60, 2),
            'attendance' => $attendance,
            'rating' => round($item->rating ?? 0, 1),
         ];
      })->values();
    }


    public function getTrendsByProgram(array $filters)
    {
        $query = DB::table('mentorship_requests')
            ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
            ->select('cycles.title as program', DB::raw('COUNT(*) as total'))
            ->groupBy('cycles.title');

        $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getPeakActivity(array $filters)
    {
        $query = DB::table('mentorship_requests')
            ->join('cycles', 'mentorship_requests.cycle_id', '=', 'cycles.id')
            ->selectRaw('DAYNAME(zoom_date) as day, COUNT(*) as total')
            ->groupBy('day');

        $this->applyFilters($query, $filters);

        return $query->get()->pluck('total', 'day');
    }

}
