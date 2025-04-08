<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\VerifyEmailQueued;

class User extends Authenticatable implements MustVerifyEmail
{
    // use Auditable;
    use Notifiable;
    use HasRoles;
    // use SoftDeletes;
    public static $module = 'Users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'guardian', 'gender', 'age', 'country_code', 'phone', 'country_code_w_a', 'whatsapp', 'email', 'new_email', 'title_ar', 'title_en', 'HaveCompany', 'company_id','country_id', 'state_id', 'city_id', 'password', 'api_token', 'provider', 'provider_id', 'google_id','email_verified_at','img_url',
        'PageId','dob','academic_stat','university','faculty','employer','expertise', 'language','currency','timezone','houre_rate','zip_code','address', 'google2fa_secret'];
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
        if( !in_array(env('APP_ENV', 'local'), ['local']) )
        {
            try {
                $this->notify(new ResetPasswordNotification($token));
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
	}
    public function sendEmailVerificationNotification()
    {
        if( !in_array(env('APP_ENV', 'local'), ['local']) )
        {
            try {
                $this->notify(new Notifications\VerifyEmailQueued($this->first_name_en.' ' .$this->last_name_en));
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
	}

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public static function image_handler( $img )
    {
        if( !$img ) { return "/images/placeholder.jpg"; }
        if( $img && !str_starts_with($img, 'https://') ) { return "/191014/" . $img; }
        return $img;
    }
    // ==========> Attributes <=====================
    public function getImageUrlAttribute()
    {
        $image = $this->img_url;
        return self::image_handler( $image );
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

	public function roles() { return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id'); }
	public function allotRoleById( $id )
    {
        $user_id = $this->id;
        $_role = Role::find( $id );
        if( !empty( $_role ) )
        {
            RoleUsers::updateOrCreate(
                ['model_id' => $this->id, 'role_id' => $id, 'model_type' => 'App\User'],
            );
        }
    }
    public function allotRole( $role )
    {
        $user_id = $this->id;
        $_role = Role::where('default_for', 'LIKE', '%' . $role . '%')->first();
        if( !empty( $_role ) )
        {
            $id = $_role->id;
        }
        RoleUsers::updateOrCreate(
            ['model_id' => $user_id, 'role_id' => $id, 'model_type' => 'App\User'],
        );
    }
    
    public function hasRole( $name ) 
    {
        if( env('PERMISSION_BYPASS', false) === true ) { return true; }
        $name = trim( strtolower($name) );
        $roles = $this->roles->pluck('default_for')->toArray();
        $roles = array_map(function($n){ return trim( strtolower($n) ); }, $roles);
        return in_array($name, $roles);
    }
    public function hasAnyRole( $roles )
    {
        if( env('PERMISSION_BYPASS', false) === true ) { return true; }
        $rules = array_map(function($n){ return trim( strtolower($n) ); }, $this->roles->pluck('default_for')->toArray());
        if( !is_array( $roles ) ) { $roles = explode('|', $roles); }
        $roles = array_map(function($n){ return trim( strtolower( $n ) ); }, $roles);
        foreach( $roles as $role ) {  if( in_array($role, $rules) ) { return true; } }
        return false;
    }

    public function myPermissions()
    {
        $permissions = $this->roles->flatMap(function($role) { return $role->_permissions->pluck('name'); })->unique()->values()->toArray();
        return $permissions;
    }
	public function ableTo( $permission, $permissions = [] )
	{
        if( env('PERMISSION_BYPASS', false) === true ) { return true; }
        if( empty( $permissions ) || count( $permissions ) == 0 ) { $permissions = $this->roles->flatMap(function($role) { return $role->_permissions->pluck('name'); })->unique()->values()->toArray(); }
        return in_array( $permission, $permissions );
	}

    public function ableToAll( $permission, $permissions = [] )
    {
        $count = 0;
        if( env('PERMISSION_BYPASS', false) === true ) { return true; }
        if( empty( $permissions ) || count( $permissions ) == 0 ) { $permissions = $this->roles->flatMap(function($role) { return $role->_permissions->pluck('name'); })->unique()->values()->toArray(); }
        $permissions = array_map(function($n){ return trim( strtolower($n) ); }, $permissions); 
        foreach( $permission as $p )
        {
            if( !in_array( trim( strtolower($p) ), $permissions ) ) { return false; } else { $count++; }
        }
        return $count == count( $permission );
    }

    public function ableToAny( $permission, $permissions = [] )
    {
        if( env('PERMISSION_BYPASS', false) === true ) { return true; }
        if( empty( $permissions ) || count( $permissions ) == 0 ) { $permissions = $this->roles->flatMap(function($role) { return $role->_permissions->pluck('name'); })->unique()->values()->toArray(); }
        $permissions = array_map(function($n){ return trim( strtolower($n) ); }, $permissions); 
        foreach( $permission as $p )
        {
            if( in_array( trim( strtolower($p) ), $permissions ) ) { return true; }
        }
        return false;
    }
}