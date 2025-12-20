<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Settings extends Model
{
    // use Auditable;
    public static $module = 'App Settings';
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['PUSHER_APP_KEY', 'PUSHER_APP_SECRET', 'PUSHER_APP_ID', 'PUSHER_APP_CLUSTER', 'BroadCast', 'max_concurrent_sessions'];

    protected $casts = [
        'enable_email' => 'boolean',
        'enable_whatsapp' => 'boolean',
        'enable_pusher' => 'boolean',
    ];

    public static function hasReachedMaxConcurrentSessions($startTime = null, $duration = null)
    {
        $settings = self::where('id', 1)->first();
        if (!$settings || empty($settings->max_concurrent_sessions)) {
            return false;
        }

        $maxLimit = (int) $settings->max_concurrent_sessions;
        
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
        $count += \DB::table('training_sessions')
            ->whereNotNull('zoom_id')
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('datetime', '<', (string) $endTime->timestamp)
                      ->where('enddatetime', '>', (string) $checkTime->timestamp);
            })
            ->count();

        // Count overlapping final screenings (stored as datetime)
        $count += \DB::table('finalscreenings')
            ->whereNotNull('zoom_id')
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('datetime', '<', $endTime->toDateTimeString())
                      ->whereRaw('DATE_ADD(datetime, INTERVAL duration MINUTE) > ?', [$checkTime->toDateTimeString()]);
            })
            ->count();

        // Count overlapping mentorship requests (stored as datetime)
        $count += \DB::table('mentorship_requests')
            ->whereNotNull('zoom_id')
            ->where(function($query) use ($checkTime, $endTime) {
                $query->where('zoom_date', '<', $endTime->toDateTimeString())
                      ->whereRaw('DATE_ADD(zoom_date, INTERVAL period MINUTE) > ?', [$checkTime->toDateTimeString()]);
            })
            ->count();

        return $count >= $maxLimit;
    }
}
