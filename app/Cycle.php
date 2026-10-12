<?php

namespace App;

use URL;
use App\Step;
use Carbon\Carbon;
use App\Traits\Auditable;
use App\Traits\HasFileCleanup;
use App\Services\TimezoneService;
use App\Services\PlatformBrandingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use stdClass;

class Cycle extends Model
{
    public const TITLE_MAX_LENGTH = 50;

    use HasFactory, Auditable, HasFileCleanup;
    public static $module = 'Program';
    protected array $fileFields = [
        'img_url' => 'public_uploads',
        'program_guideline_file' => 'public_uploads',
        'financial_guideline_file' => 'public_uploads',
        'legal_guideline_file' => 'public_uploads',
        'delegation_file' => 'public_uploads',
    ];

    protected $casts = [
        'start'  => 'date:Y-m-d',
        'end' => 'date:Y-m-d',
        'is_public' => 'boolean',
        'landing_visible' => 'boolean',
        'landing_sort_order' => 'integer',
    ];
    protected $guarded = [];

    // =============> Attributes <======================

    public function getNameAttribute()
    {
        return $this->{"name_".app()->getLocale()};
    }
    public function getStartFormatAttribute()
    {
        return date('Y-m-d',strtotime($this->attributes['start']));
    }
    public function getEndFormatAttribute()
    {
        return date('Y-m-d',strtotime($this->attributes['end']));
    }
    // GV-9566: `end` is an inclusive DATE in the program's platform zone;
    // isPast() on its midnight expired the program on the morning of its last day.
    public function getIsExpiredAttribute()
    {
        return $this->end
            && $this->end->format('Y-m-d') < TimezoneService::platformToday(TimezoneService::platform($this->organization_id));
    }

    public function getLogoAttribute()
    {
        if ($this->img_url) {
            return app(\App\Services\FileManagerService::class)->getUrl($this->img_url, 'public_uploads');
        }

        if ($this->organization && $this->organization->img_url) {
            return route('organizations.logo', $this->organization->img_url ?? '');
        }

        return asset('/images/background/logo.png');
    }

    public function getProgramGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->program_guideline_file, 'public_uploads');
    }

    public function getFinancialGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->financial_guideline_file, 'public_uploads');
    }

    public function getLegalGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->legal_guideline_file, 'public_uploads');
    }

    public function getDelegationFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->delegation_file, 'public_uploads');
    }
    // =============> Relations <========================
    public function organization(){
        return $this->belongsTo(Organization::class,'organization_id');
    }
    public function companies(){
        return $this->hasMany(Company::class,'cycle');
    }
    public function mentors(){
        return $this->belongsToMany(User::class, 'cycles_mentors', 'cycle_id', 'user_id');
    }
    public function CalenderSync()
    {
        return $this->morphMany(CalenderSync::class, 'calenderable');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'cycle_project')->withPivot(['status','created_at'])->withTimestamps(); 
    }

    public function mentorshipRequests()
    {
        return $this->hasMany(MentorshipRequest::class, 'cycle_id', 'id');
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    public function trainingSessions()
   {
      return $this->hasManyThrough(TrainingSession::class, Step::class);
   }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponses::class);
    }

    public static function programTypes(): array
    {
        $types = ['General_Program', 'Hackathon'];
        return collect($types)->mapWithKeys(function ($type) {
            return [$type => __('cycles.' . $type)];
        })->toArray();
    }
    public function custom_pages()
    {
        return $this->hasMany(CycleCustomPages::class);
    }

    public function focusAreas()
    {
        return $this->belongsToMany(
            StaticCategory::class,
            'cycle_focus_areas',
            'cycle_id',
            'static_category_id'
        )->withTimestamps();
    }

    public function benefits()
    {
        return $this->hasMany(CycleBenefit::class)->orderBy('sort_order');
    }

    public function scopeLandingVisible($query)
    {
        return $query->where('landing_visible', true);
    }

    public function getProgress()
    {
        $totalSteps = Step::where('cycle_id', $this->id)->count();

        // GV-9566: a step is still running through its `to` day in the platform zone.
        $today = TimezoneService::platformToday(TimezoneService::platform($this->organization_id));
        $totalStepsFinish = \App\Step::where('to', '>=', $today)->where('cycle_id', $this->id)->count();

        return $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
    }

    public static function getHackathon( $id )
    {
        $hackathon = DB::table('cycles as c')
            ->select([
                'c.id',
                'c.title',
                'c.description',
                'c.start',
                'c.end',
                'c.winners_announcement',
                'c.winners_visibility',
                'o.id AS organization_id',
                'o.name_ar AS organization_name_ar',
                'o.name_en AS organization_name_en',
            ])
            ->leftJoin('organizations as o', 'c.organization_id', '=', 'o.id')
            ->where('c.type', 'Hackathon')
            ->whereNull('c.deleted_at')
            ->where('c.id', $id)
            ->first();

        return $hackathon;
    }

    public function getStartColorAttribute($value)
    {
        return $value ?: PlatformBrandingService::programDefaults()['start_color'];
    }

    public function getEndColorAttribute($value)
    {
        return $value ?: PlatformBrandingService::programDefaults()['end_color'];
    }

    public function getMainColorAttribute($value)
    {
        return $value ?: PlatformBrandingService::programDefaults()['main_color'];
    }
}
