<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use App\Services\TimezoneService;
use Carbon\Carbon;

class Settings extends Model
{
    use HasFactory;
    // use Auditable;
    public static $module = 'App Settings';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_ID', 'PUSHER_APP_CLUSTER', 'BroadCast', 'max_concurrent_sessions', 'email_provider', 'resend_api_key'];

    protected $casts = [
        'enable_email' => 'boolean',
        'enable_whatsapp' => 'boolean',
        'enable_pusher' => 'boolean',
    ];

    protected static function booted()
    {
        // TimezoneService caches the platform zone (GV-9566).
        static::saved(fn () => TimezoneService::forgetPlatform());
    }

    public static function hasReachedMaxConcurrentSessions($startTime = null, $duration = null, $organization_id = null)
    {
        $maxLimit = 0;
        if ($organization_id) {
            $org = \DB::table('organizations')->where('id', $organization_id)->first();
            if ($org && !empty($org->max_concurrent_sessions)) {
                $maxLimit = (int) $org->max_concurrent_sessions;
            }
        }

        if ($maxLimit === 0) {
            $settings = self::where('id', 1)->first();
            if (!$settings || empty($settings->max_concurrent_sessions)) {
                return false;
            }
            $maxLimit = (int) $settings->max_concurrent_sessions;
        }

        // GV-9566: work on real instants. A plain string is wall-clock typed in the
        // platform zone (not the server's); training sessions store unix timestamps,
        // final screenings and mentorship sessions store platform wall-clock, so each
        // source is compared in its own terms. Rows are read in the organization's
        // zone when one is given, else the global platform zone.
        $zone = TimezoneService::platform($organization_id ? (int) $organization_id : null);

        // If no time is provided, check the current time
        if ($startTime) {
            $checkTime = $startTime instanceof \DateTimeInterface
                ? Carbon::instance($startTime)
                : TimezoneService::wallClock($startTime, $zone);
        }
        $checkTime = $checkTime ?? now();
        
        $duration = $duration ?? 0;
        $endTime = (clone $checkTime)->addMinutes($duration);
        $checkClock = $checkTime->copy()->setTimezone($zone)->toDateTimeString();
        $endClock = $endTime->copy()->setTimezone($zone)->toDateTimeString();

        $count = 0;

        // Count overlapping training sessions (stored as varchar timestamps)
        $trainingQuery = \DB::table('training_sessions')
            ->whereNull('training_sessions.deleted_at')
            ->whereNotNull('training_sessions.zoom_id')
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('training_sessions.datetime', '<', (string) $endTime->timestamp)
                      ->where('training_sessions.enddatetime', '>', (string) $checkTime->timestamp);
            });
        
        if ($organization_id) {
            $trainingQuery->join('steps', 'training_sessions.step_id', '=', 'steps.id')
                ->join('cycles', 'steps.cycle_id', '=', 'cycles.id')
                ->where('cycles.organization_id', $organization_id);
        }
        $count += $trainingQuery->count();

        // Count overlapping final screenings (stored as datetime)
        $screeningQuery = \DB::table('finalscreenings')
            ->whereNull('finalscreenings.deleted_at')
            ->whereNotNull('finalscreenings.zoom_id')
            ->where(function($query) use ($checkClock, $endClock) {
                $query->where('finalscreenings.datetime', '<', $endClock)
                      ->whereRaw('DATE_ADD(finalscreenings.datetime, INTERVAL finalscreenings.duration MINUTE) > ?', [$checkClock]);
            });

        if ($organization_id) {
            $screeningQuery->join('steps', 'finalscreenings.step_id', '=', 'steps.id')
                ->join('cycles', 'steps.cycle_id', '=', 'cycles.id')
                ->where('cycles.organization_id', $organization_id);
        }
        $count += $screeningQuery->count();

        // Count overlapping mentorship requests (stored as datetime)
        $mentorshipQuery = \DB::table('mentorship_requests')
            ->whereNull('mentorship_requests.deleted_at')
            ->whereNotNull('mentorship_requests.zoom_id')
            ->where(function($query) use ($checkClock, $endClock) {
                $query->where('mentorship_requests.zoom_date', '<', $endClock)
                      ->whereRaw('DATE_ADD(mentorship_requests.zoom_date, INTERVAL mentorship_requests.period MINUTE) > ?', [$checkClock]);
            });

        if ($organization_id) {
            $mentorshipQuery->where('mentorship_requests.organization_id', $organization_id);
        }
        $count += $mentorshipQuery->count();

        return $count >= $maxLimit;
    }
}
