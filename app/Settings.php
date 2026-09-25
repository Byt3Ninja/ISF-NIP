<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Carbon\Carbon;

class Settings extends Model
{
    use HasFactory;
    // use Auditable;
    public static $module = 'App Settings';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_ID', 'PUSHER_APP_CLUSTER', 'BroadCast', 'max_concurrent_sessions', 'email_provider', 'smtp_preset', 'resend_api_key', 'resend_domain', 'resend_from_address', 'resend_from_name', 'resend_connected_at'];

    protected $casts = [
        'enable_email' => 'boolean',
        'enable_whatsapp' => 'boolean',
        'enable_pusher' => 'boolean',
        'resend_connected_at' => 'datetime',
    ];

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

        // If no time is provided, check the current time
        if ($startTime) {
            if (is_numeric($startTime)) {
                $checkTime = Carbon::createFromTimestamp($startTime);
            } else {
                $checkTime = Carbon::parse($startTime);
            }
        } else {
            $checkTime = now();
        }
        
        $duration = $duration ?? 0;
        $endTime = (clone $checkTime)->addMinutes($duration);

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
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('finalscreenings.datetime', '<', $endTime->toDateTimeString())
                      ->whereRaw('DATE_ADD(finalscreenings.datetime, INTERVAL finalscreenings.duration MINUTE) > ?', [$checkTime->toDateTimeString()]);
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
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('mentorship_requests.zoom_date', '<', $endTime->toDateTimeString())
                      ->whereRaw('DATE_ADD(mentorship_requests.zoom_date, INTERVAL mentorship_requests.period MINUTE) > ?', [$checkTime->toDateTimeString()]);
            });

        if ($organization_id) {
            $mentorshipQuery->where('mentorship_requests.organization_id', $organization_id);
        }
        $count += $mentorshipQuery->count();

        return $count >= $maxLimit;
    }
}
