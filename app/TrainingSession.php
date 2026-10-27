<?php

namespace App;

use App\Traits\Auditable;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    // use Auditable;.
    // stype
    // 1: Custom Form
    // 2: Screening Step
    // 3: Mentorship Step
    // 4: Training Step
    // 5: Final screening Step
    protected $table = 'training_sessions';
    public static $module = 'Training Sessions';
    protected $fillable = ['step_id', 'title', 'dsc', 'datetime', 'enddatetime', 'duration', 'zoom_id', 'zoom_passcode', 'start_url', 'join_url', 'trainer_name', 'trainer_email', 'training_Address', 'training_location', 'QrCode', 'user_id', 'type'];
    protected $casts = ['datetime' => 'date:Y-m-d H:i',];

    /**
     * GV-9566: `datetime` / `enddatetime` are unix timestamps (true instants).
     * Returns one as a Carbon in $zone (default: the step's platform zone), then
     * in $viewer's own zone when given and set. Admin edit forms pass no viewer so
     * they get the platform wall-clock that was typed, and an unchanged edit
     * round-trips to the same timestamp.
     */
    public static function at($stamp, ?string $zone = null, $viewer = null): ?Carbon
    {
        $zone = $zone ?? TimezoneService::platform();
        $at = TimezoneService::wallClock($stamp, $zone);

        return $at ? TimezoneService::forViewer($at->setTimezone($zone), $viewer) : null;
    }

    public function zone(): string
    {
        return TimezoneService::forStep($this->step_id);
    }

    public function startsAt($viewer = null): ?Carbon
    {
        return self::at($this->getRawOriginal('datetime'), $this->zone(), $viewer);
    }

    public function endsAt($viewer = null): ?Carbon
    {
        return self::at($this->getRawOriginal('enddatetime'), $this->zone(), $viewer);
    }

    // ==========> Attributes <============
    // public function getStartFormatAttribute()
    // {
    //     return date('Y-m-d h:i a', strtotime($this->attributes['datetime']));
    // }
    // public function getStartOrgAttribute()
    // {
    //     return date('Y-m-d H:i', strtotime($this->attributes['datetime']));
    // }
    // GV-9566: `datetime` is a unix timestamp; strtotime() on it returned false (1970).
    public function getEndFormatAttribute()
    {
        return $this->startsAt(Auth::user())?->addMinutes((int) $this->attributes['duration'])->format('Y-m-d h:i a');
    }
    // "org" = platform wall-clock, like the other calendar feed entries (no viewer conversion).
    public function getEndOrgAttribute()
    {
        return $this->startsAt()?->addMinutes((int) $this->attributes['duration'])->format('Y-m-d H:i');
    }
    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id');
    }

    public function course()
    {
        return $this->belongsTo(Training::class, 'training_Address');
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

    public function reports()
    {
        return $this->hasMany(SessionReport::class, 'session_id');
    }

    public function recordings()
    {
        return $this->hasMany(SessionRecording::class, 'session_id');
    }
    public function attendance()
    {
        return $this->hasMany(SessionAttendance::class, 'session_id');
    }
}