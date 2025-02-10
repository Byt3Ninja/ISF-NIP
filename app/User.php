<?php

namespace App;

use App\Traits\Auditable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
//    use Auditable;
    use Notifiable;
    use HasRoles;
//    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'guardian', 'gender', 'age', 'country_code', 'phone', 'country_code_w_a', 'whatsapp', 'email', 'new_email', 'title_ar', 'title_en', 'HaveCompany', 'company_id','country_id', 'state_id', 'city_id', 'password', 'api_token', 'provider', 'provider_id', 'google_id','email_verified_at','img_url',
        'PageId','dob','academic_stat','university','faculty','employer','expertise', 'language','currency','timezone','houre_rate','zip_code','address'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

	public function sendPasswordResetNotification($token)
	{
    	$this->notify(new ResetPasswordNotification($token));
	}
    public function sendEmailVerificationNotification()
    {
    $this->notify(new Notifications\VerifyEmailQueued($this->first_name_en.' ' .$this->last_name_en));
	}

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // ==========> Attributes <=====================
    public function getImageUrlAttribute()
    {
        $image = $this->img_url;
        if (!$image) {
            return "/images/placeholder.jpg";
        }
        if ($image && !str_starts_with($image, 'https://')) {
            return "/191014/" . $image;
        }
        return $image;
    }
    public function getFirstNameAttribute()
    {
        return $this->{"first_name_".app()->getLocale()};
    }
    public function getLastNameAttribute()
    {
        return $this->{"last_name_".app()->getLocale()};
    }
    public function getNameAttribute()
    {
        return $this->{"first_name_".app()->getLocale()} .' '.$this->{"last_name_".app()->getLocale()};
    }
    public function getTitleAttribute()
    {
        return $this->{"title_".app()->getLocale()};
    }

    // ===============> Relations <===================

    // ===============> General Relations <==============
    public function oauthToken()
    {
        return $this->hasOne(UserOauthToken::class);
    }
    public function CalenderSync()
    {
        return $this->hasMany(CalenderSync::class, 'user_id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class,'country_id');
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function calendar() {
        return $this->hasMany(Calendar::class);
    }
    public function myServiceRequests(){
        return $this->hasMany(ServicesRequests::class,'user_id');
    }
    // ============> company (startup) Relations <====================
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function mentorship() {
        return $this->hasMany(MentorshipRequest::class,'user_id');
    }

    // =================> mentor Relations <=============================
    public function bio() {
        return $this->hasOne(UsersBio::class);
    }
    public function mentorSessions() {
        return $this->hasMany(MentorshipRequest::class,'mentor_id');
    }
    public function mrating(){
        return $this->hasMany(MentorshipRequest::class,'mentor_id')->avg('rating');
    }
    public function mentorCycles(){
        return $this->belongsToMany(Cycle::class, 'cycles_mentors', 'user_id', 'cycle_id');
    }
    public function mentorExpertises(){
        return $this->belongsToMany(Expertise::class, 'mentor_expertise', 'mentor_id', 'expertise_id');
    }

    // ===============>  Organization Manager Relations < =========================
    public function organization(){
        return $this->hasOne(Organization::class,'user_id');
    }

    // ============> Judger Relations <======================
    public function screeningRequests(){
        return $this->hasMany(Screening::class,'user_id');
    }

    public function tags()
    {
        return $this->hasMany(Tags::class,'user_id');
    }
    public function mentorshipRequests()
    {
        return $this->hasMany(MentorshipRequest::class, 'mentor_id');
    }


}