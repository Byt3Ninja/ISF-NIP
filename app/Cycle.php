<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    use Auditable;
    public static $module = 'Program';
    protected $casts = ['start'  => 'date:Y-m-d', 'end' => 'date:Y-m-d',];
    protected $fillable = [
       'title', 'description', 'start', 'end', 'organization_id',
    ];

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

    public function getProgramGuidelineFileAttribute($value)
    {
        return $value ? 'programs/' . basename($value) : null;
    }

    
    public function getFinancialGuidelineFileAttribute($value)
    {
        return $value ? 'financials/' . basename($value) : null;
    }

   
    public function getLegalGuidelineFileAttribute($value)
    {
        return $value ? 'legal_policies/' . basename($value) : null;
    }

    public function getDelegationFileAttribute($value)
    {
        return $value ? 'delegations/' . basename($value) : null;
    }

    


}
