<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Auditable;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar','gender', 'phone', 'email', 'title_ar', 'title_en', 'HaveCompany', 'company_id','country_id', 'state_id', 'city_id', 'password', 'provider', 'provider_id', 'google_id','email_verified_at','img_url',
        'PageId','dob','academic_stat','university','faculty','employer','expertise'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    //// New
	public function sendPasswordResetNotification($token)
	{
    	$this->notify(new ResetPasswordNotification($token));
	}
    public function sendEmailVerificationNotification()
    {
    $this->notify(new Notifications\VerifyEmailQueued($this->first_name_en.' ' .$this->last_name_en));
	}
	/// By Ibrahim Elsayed
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
        return "/191014/".$image;
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
    public function company()
    {
        return $this->belongsTo(Company::class);
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
    public function mentorship() {
        return $this->hasMany(MentorshipRequest::class);
    }
    public function bio() {
        return $this->hasOne(UsersBio::class);
    }
    public function mrating(){
        return $this->hasMany(MentorshipRequest::class,'mentor_id')->avg('rating');
//        return (MentorshipRequest::where('mentor_id',$this->id)->avg('rating'));
    }
    public function organization(){
        return $this->hasOne(Organization::class,'user_id');
    }
}