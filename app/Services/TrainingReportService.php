<?php
namespace App\Services;

use App\TrainingSession;
use App\SessionReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingReportService
{
    protected function baseQuery(array $filters)
    {
        return TrainingSession::query()
            ->when(isset($filters['program']), function ($q) use ($filters) {
                $q->whereHas('step.cycle', function ($q2) use ($filters) {
                    $q2->where('title', $filters['program']);
                });
            })
            // GV-9566: `datetime` is a unix timestamp, so whereDate() never matched;
            // compare it with the day boundaries in the platform zone.
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                $q->where('datetime', '>=', $this->dayBoundary($filters['date_from'], false));
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                $q->where('datetime', '<=', $this->dayBoundary($filters['date_to'], true));
            });
    }

    /** Start (or end) of a Y-m-d day in the platform zone, as a unix timestamp. */
    protected function dayBoundary($date, bool $end): int
    {
        $day = Carbon::parse($date, TimezoneService::platform());

        return ($end ? $day->endOfDay() : $day->startOfDay())->timestamp;
    }

    public function getSummary(array $filters)
    {
        $query = $this->baseQuery($filters);

        $sessions = $query->get();

        $totalSessions = $sessions->count();
        $totalHours = $sessions->sum('duration') / 60;
        $avgPerSession = $totalSessions ? round($totalHours / $totalSessions, 2) : 0;

        $zoomSessions = $sessions->where('type', 'sessions')->count();
        $onlineSessions = $sessions->where('type', 'online')->count();
        $offlineSessions = $sessions->where('type', 'offline')->count();

        $zoomShare = $totalSessions ? round(($zoomSessions / $totalSessions) * 100, 2) : 0;

        $completedSessions = $sessions->filter(fn ($s) => $s->enddatetime !== null)->count();
        $completionRate = $totalSessions ? round(($completedSessions / $totalSessions) * 100, 2) : 0;

        $sessionIds = $sessions->pluck('id');

        $attendanceData = SessionReport::whereIn('session_id', $sessionIds)->get();
        $attendanceDuration = $attendanceData->sum('attendance_duration');
        $expectedDuration = $sessions->sum('duration');

        $avgAttendance = ($expectedDuration > 0)
            ? round(($attendanceDuration / $expectedDuration) * 100, 2)
            : 0;

        return [
            'totalSessions' => $totalSessions,
            'totalHours' => round($totalHours, 2),
            'zoomSessions' => $zoomSessions,
            'onlineSessions' => $onlineSessions,
            'offlineSessions' => $offlineSessions,
            'zoomShare' => $zoomShare,
            'avgPerSession' => $avgPerSession,
            'completionRate' => $completionRate,
            'avgAttendance' => $avgAttendance,
        ];
    }

    public function getSessionTypes(array $filters)
    {
        $query = $this->baseQuery($filters);

        $types = $query->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');

        return $types;
    }

    public function getMonthlyTrends(array $filters)
    {
        $query = $this->baseQuery($filters);

        return $query->selectRaw("DATE_FORMAT(FROM_UNIXTIME(datetime), '%b') as month, COUNT(*) as sessions")
        ->groupBy('month')
        ->orderByRaw("STR_TO_DATE(CONCAT('01 ', month, ' 2000'), '%d %b %Y')")
        ->pluck('sessions', 'month')
        ->toArray();
    }

    public function getTypeBreakdown(array $filters)
    {
        $query = $this->baseQuery($filters);

        $types = $query->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        return [
            'labels' => $types->pluck('type'),
            'counts' => $types->pluck('count')
        ];
    }

   public function getHoursTrend(array $filters)
    {
        $query = $this->baseQuery($filters)
        ->whereNotNull('datetime')                        
        ->whereRaw("datetime REGEXP '^[0-9]+$'");      

        return $query->selectRaw("
            DATE_FORMAT(FROM_UNIXTIME(CAST(datetime AS UNSIGNED)), '%b') as month,
            SUM(duration)/60 as hours
        ")
        ->groupBy('month')
        ->orderByRaw("STR_TO_DATE(CONCAT('01 ', month, ' 2000'), '%d %b %Y')")
        ->get()
        ->pluck('hours', 'month')
        ->map(fn($hours) => $hours !== null ? round((float) $hours, 2) : 0)
        ->toArray();
    }
   

    public function getTrainerPerformance(array $filters)
    {
        $query = $this->baseQuery($filters);

        return $query->select('trainer_name')
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('SUM(duration)/60 as hours')
            ->whereNotNull('trainer_name')
            ->groupBy('trainer_name')
            ->get()
            ->map(function ($trainer) {
                $attendees = SessionReport::where('participant_name', $trainer->trainer_name)->count();
                return [
                    'name' => $trainer->trainer_name,
                    'sessions' => $trainer->sessions,
                    'hours' => round($trainer->hours, 2),
                    'attendees' => $attendees,
                ];
            });
    }


     public function getTopics(array $filters)
    {
        $query = $this->baseQuery($filters);

        return $query->select('title')
        ->selectRaw('COUNT(*) as sessions')
        ->selectRaw('AVG(duration) as avg_duration')
        ->whereNotNull('title')
        ->groupBy('title')
        ->get()
        ->map(function ($topic) {
            $sessionIds = TrainingSession::where('title', $topic->title)->pluck('id');

            $reports = SessionReport::whereIn('session_id', $sessionIds)->get();

            $totalAttendees = $reports->count();

            $completed = $reports->where('attendance_duration', '>=', 20)->count();

            $completionRate = $totalAttendees > 0
                ? round(($completed / $totalAttendees) * 100, 1)
                : 0;

            return [
                'name' => $topic->title,
                'sessions' => $topic->sessions,
                'avgAttendees' => $topic->sessions > 0
                    ? round($totalAttendees / $topic->sessions, 1)
                    : 0,
                'completion' => $completionRate,
            ];
        });
    }

    public function getRecentSessions(array $filters)
    {
       return $this->baseQuery($filters)
        ->whereNotNull('datetime')
        ->orderByDesc('datetime')
        ->take(10)
        ->get()
        ->map(function ($s) {
            return [
                'id' => $s->id,
                // GV-9566: shown in the viewer's own timezone.
                'date' => optional($s->startsAt(Auth::user()))->format('Y-m-d H:i'),
                'title' => $s->title,
                'type' => $s->type,
                'trainer' => $s->trainer_name,
                'attendees' => SessionReport::where('session_id', $s->id)->count(),
                'duration' => round($s->duration / 60, 2),
            ];
        });
    }
}
