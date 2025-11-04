<?php

namespace App;

use App\Step;
use Carbon\Carbon;
use App\Traits\Auditable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Cycle extends Model
{
    use Auditable;
    public static $module = 'Program';
    protected $casts = ['start'  => 'date:Y-m-d', 'end' => 'date:Y-m-d', 'is_public' => 'boolean',];
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

    public function getProgress()
    {
        $totalSteps = Step::where('cycle_id', $this->id)->count();

        $totalStepsFinish = \App\Step::where('to', '>', Carbon::now())->where('cycle_id', $this->id)->count();

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

   
}
