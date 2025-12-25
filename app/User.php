<?php

namespace App;

use Illuminate\Support\Str;
use App\Http\Middleware\Permissions;
use App\Traits\Auditable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Namu\WireChat\Traits\Chatable;
use Namu\WireChat\Models\Conversation;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Notifications\Notifiable;
use Namu\WireChat\Enums\ParticipantRole;
use Namu\WireChat\Enums\ConversationType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Namu\WireChat\Models\Message as WireChatMessage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    // use Auditable;
    use Notifiable ,HasRoles,HasApiTokens, Chatable;
    // use SoftDeletes;
    public static $module = 'Users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'guardian', 'gender', 'age', 'country_code', 'phone', 'country_code_w_a', 'whatsapp', 'email', 'new_email', 'title_ar', 'title_en', 'HaveCompany', 'company_id','country_id', 'state_id', 'city_id', 'password', 'api_token', 'provider', 'provider_id', 'google_id','email_verified_at','img_url',
        'PageId','dob','academic_stat','university','faculty','employer','expertise', 'language','currency','timezone','houre_rate','zip_code','address', 'google2fa_secret', 'accept_email', 'accept_whatsapp', 'accept_pusher'];
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

            try {
                $this->notify(new ResetPasswordNotification($token));
            } catch (\Throwable $th) {
                 throw $th;
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
        else
        {
            if( empty( $this->email_verified_at ) )
            {
                $this->email_verified_at = now();
                $this->save();
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
        'accept_email' => 'boolean',
        'accept_whatsapp' => 'boolean',
        'accept_pusher' => 'boolean',
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
        return $this->hasOne(UsersBio::class, 'user_id');
    }
    public function schedule() {
        return $this->hasMany(Schedule::class, 'user_id');
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
        return $this->belongsTo(Organization::class)->withTrashed();
    }

    /**
     * Many-to-many relationship for Sub-Admin users with multiple organizations.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'user_organizations')->withTimestamps();
    }

    /**
     * Get all accessible organization IDs for this user.
     * - Admin: returns all organization IDs
     * - Sub-Admin: returns assigned organization IDs from pivot table
     * - organizationManager: returns single organization_id
     * - Others: returns organization_id if set
     *
     * @return array
     */
    public function getOrganizationsIdsAttribute(): array
    {
        if ($this->hasRole('admin')) {
            return Organization::pluck('id')->toArray();
        }

        if ($this->hasRole('subadmin')) {
            return $this->organizations()->pluck('organizations.id')->toArray();
        }

        if ($this->organization_id) {
            return [$this->organization_id];
        }

        return [];
    }

    /**
     * Check if user can access a specific organization.
     *
     * @param int $organizationId
     * @return bool
     */
    public function canAccessOrganization(int $organizationId): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return in_array($organizationId, $this->organizations_ids);
    }

    /**
     * Check if user is a Sub-Admin.
     *
     * @return bool
     */
    public function isSubAdmin(): bool
    {
        return $this->hasRole('subadmin');
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

    public function scores()
    {
        return $this->hasMany(UserScore::class,'user_id');
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
    public function deAllotRole( $role )
    {
        $user_id = $this->id;
        $_role = Role::where('default_for', 'LIKE', '%' . $role . '%')->first();
        if( !empty( $_role ) )
        {
            $id = $_role->id;
            RoleUsers::where(['model_id' => $user_id, 'role_id' => $id, 'model_type' => 'App\User'])->delete();
        }
    }
    public function allotRole( $role )
    {
        $user_id = $this->id;
        $_role = Role::where('default_for', 'LIKE', '%' . $role . '%')->first();
        if( !empty( $_role ) )
        {
            $id = $_role->id;
            RoleUsers::updateOrCreate(
                ['model_id' => $user_id, 'role_id' => $id, 'model_type' => 'App\User'],
            );
        }
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
        if( $this->checkForBypass( $permission ) === true ) { return true; }
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
        if( $this->checkForBypassToAny( $permission ) === true ) { return true; }
        if( empty( $permissions ) || count( $permissions ) == 0 ) { $permissions = $this->roles->flatMap(function($role) { return $role->_permissions->pluck('name'); })->unique()->values()->toArray(); }
        $permissions = array_map(function($n){ return trim( strtolower($n) ); }, $permissions); 
        foreach( $permission as $p )
        {
            if( in_array( trim( strtolower($p) ), $permissions ) ) { return true; }
        }
        return false;
    }

    public function checkForBypass( $permission )
    {
        $middleware = new Permissions;
        $exceptNames = $middleware->exceptNames;
        if( in_array( $permission, $exceptNames) ) { return true; }
        else
        {
            if( is_array( $permission ) ) { return false; }
            foreach( $exceptNames as $except) { if( Str::is($except, $permission) ) { return true; } }
        }
        return false;
    }

    public function checkForBypassToAny( $permission )
    {
        $middleware = new Permissions;
        $exceptNames = $middleware->exceptNames;

        foreach( $permission as $p )
        {
            foreach( $exceptNames as $except) { if( Str::is($except, $p) ) { return true; } }
        }
        return false;
    }

    /**
    * Returns the URL for the user's cover image (avatar).
    * Adjust the 'avatar_url' field to your database setup.
    */
    public function getCoverUrlAttribute(): ?string
    {
      return $this->avatar_url ?? null;
    }

    /**
    * Returns the URL for the user's profile page.
    * Adjust the 'profile' route as needed for your setup.
    */
    public function getProfileUrlAttribute(): ?string
    {
      return route('profile', ['id' => $this->id]);
    }

    /**
    * Returns the display name for the user.
    * Modify this to use your preferred name field.
    */
    public function getDisplayNameAttribute(): ?string
    {
      return $this->name ?? 'user';
    }

    /**
    * Search for users when creating a new chat or adding members to a group.
    * Customize the search logic to limit results, such as restricting to friends or eligible users only.
    */
    public function searchChatables(string $query): ?Collection
    {
     $searchableFields = ['first_name_en', 'last_name_en', 'first_name_ar', 'last_name_ar', 'email'];
     return User::where(function ($queryBuilder) use ($searchableFields, $query) {
        foreach ($searchableFields as $field) {
                $queryBuilder->orWhere($field, 'LIKE', '%'.$query.'%');
        }
      })
        ->limit(20)
        ->get();
    }

    /**
    * Determine if the user can create new groups.
    */
    public function canCreateGroups(): bool
    {
      return $this->hasVerifiedEmail() === true;
    }

    /**
    * Determine if the user can create new groups with other users
    */
    public function canCreateChats(): bool
    {
     return $this->hasVerifiedEmail();
    }

    public function teamformation()
    {
        return $this->hasOne(TeamFormation::class, 'user_id');
    }
    public function team_member()
    {
        return $this->hasOne(Team_member::class, 'user_id')->where('acceptInvition', 'accepted');
    }

    public function teamLeader()
    {
        return $this->hasOne(Team_member::class, 'user_id')->where('isTeamLeader', 1);
    }
    /**
     * Get all group conversations the user is a participant in.
     * This will return a collection of Conversation models that are of type GROUP.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function groups()
    {
        return $this->conversations();
    }

     /**
     * Get all messages for the conversation.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WireChatMessage::class, 'sendable_id', 'id');
    }

    /**
     * Get the latest message for the conversation.
     * This is a HasOne relationship that orders by the latest message.
     *
     * @return HasOne
     */
    public function latestMessage(): HasOne
    {
        // This will return a single Message model (the latest one)
        return $this->hasOne(WireChatMessage::class, 'sendable_id', 'id')->latest('id');
        // We use latest('id') for better performance on indexed columns
        // You can also use latest('created_at') if you prefer
    }

    public function removeParticipantFromGroup(Conversation $conversation, Model $participantToRemove): bool
    {
        // 1. Basic Validation: Ensure this is a group conversation.
        abort_if(
            $conversation->type !== ConversationType::GROUP,
            403,
            'This action is only available for group conversations.'
        );

        // 2. Prevent self-removal: Admins should use exitConversation() for themselves.
        abort_if(
            $this->is($participantToRemove),
            403,
            'You cannot remove yourself using this method. Use exitConversation() instead.'
        );

        // 3. Get the participant records for both the admin and the target user.
        $adminParticipant = $conversation->participant($this);
        $targetParticipant = $conversation->participant($participantToRemove);

        // 4. Abort if the target user is not even in the conversation.
        abort_if(!$targetParticipant, 404, 'The specified user is not a member of this group.');

        // 5. Permission Checks (The most important part)
        // a) The user performing the action must be an admin or owner.
        abort_unless(
            $adminParticipant && $adminParticipant->isAdmin(),
            403,
            'You do not have permission to remove members from this group.'
        );

        // b) An admin cannot remove the owner of the group.
        abort_if(
            $targetParticipant->isOwner(),
            403,
            'You cannot remove the owner of the group.'
        );

        // c) (Optional but recommended) Prevent admins from removing other admins. Only owners can.
        if ($adminParticipant->role === ParticipantRole::ADMIN && $targetParticipant->role === ParticipantRole::ADMIN) {
            abort(403, 'An admin cannot remove another admin.');
        }

        // 6. Execute the removal
        // The removeByAdmin method already exists on your Participant model.
        // It handles creating the action record and updating the participant's state.
        $targetParticipant->removeByAdmin($this);

        return true;
    }

    public function hasCompletedProfile(): bool
    {
            return filled($this->phone)
            && filled($this->country_id)
            && filled($this->address)
            && filled($this->timezone);
    }

    public function hasCompletedStartup(): bool
    {
        return $this->company
            && filled($this->company->name_en)
            && filled($this->company->address_en)
            && filled($this->company->est_date)
            && filled($this->company->stage);
    }

    public function service_provider()
    {
        return $this->hasOne(serviceProviders::class, 'user_id');
    }

}