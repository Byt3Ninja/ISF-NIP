<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use App\Traits\HasFileCleanup;
use App\Services\TimezoneService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Organization extends Model
{
	use HasFactory, Auditable, SoftDeletes, HasFileCleanup;
	public static $module = 'Organizations';
	protected $fillable = [
		'name_ar',
		'name_en',
		'email',
		'password',
		'phone',
		'bio',
		'user_id',
		'status',
		'max_concurrent_sessions',
		'api_token',
		'created_at',
		'updated_at',
		'img_url',
		'meeting_platform',
		'zoom_client_id',
		'zoom_client_secret',
		'zoom_account_id',
		'microsoft_client_id',
		'microsoft_client_secret_value',
		'microsoft_tenant_id',
		'microsoft_client_secret_id',
		'microsoft_app_email',
		'microsoft_redirect_uri',
		'time_zone',
		'organization_type_id',
		'is_draft',
		'wizard_step',
		'slug',
		// Step 2: Contact Information
		'contact_email',
		'country_code',
		'contact_phone',
		'country_id',
		'state_id',
		'city_id',
		'address',
		'google_map_url',
		// Step 2: Social Media
		'website_url',
		'linkedin_company_url',
		'twitter_handle',
		'facebook_page_url',
		'instagram_profile_url',
		'youtube_channel_url',
		'tiktok_url',
	];

	protected array $fileFields = [
		'img_url' => 'organizations',
	];

	protected $casts = [
		'is_draft' => 'boolean',
		'status' => 'boolean',
	];

	protected static function booted()
	{
		// TimezoneService caches each organization's zone (GV-9566).
		static::saved(fn ($organization) => TimezoneService::forgetOrganization($organization->id));
		static::deleted(fn ($organization) => TimezoneService::forgetOrganization($organization->id));
	}

	public static function image_handler($img)
	{
		if (!$img) {
			return '/images/logo-placeholder.png';
		}
		return $img;
	}

	// ===========> Attributes <==============
	public function getNameAttribute($key)
	{
		return $this->attributes['name_' . app()->getLocale()];
	}

	/**
	 * Get the logo URL - handles both old public path and new storage path.
	 */
	public function getImageUrlAttribute()
	{
		$image = $this->img_url;

		if (!$image) {
			return '/images/logo-placeholder.png';
		}

		if (str_starts_with($image, 'organizations/')) {
			return asset($image);
		}

		return app(\App\Services\FileManagerService::class)->getUrl($image, 'organizations');
	}

	/**
	 * Get the raw logo filename for storage operations.
	 */
	public function getLogoFilenameAttribute()
	{
		return $this->img_url;
	}
	// =============> Relations <=================

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function questionsCategories()
	{
		return $this->hasMany(QuestionCategories::class, 'organization_id', 'id');
	}

	public function cycles()
	{
		return $this->hasMany(Cycle::class);
	}

	public function questions()
	{
		return $this->belongsToMany(QuestionsBank::class, 'organization_question_bank', 'organization_id', 'question_id');
	}

	public function organizationSettings()
	{
		return $this->hasOne(OrganizationSettings::class, 'organization_id');
	}

	/**
	 * Users assigned to this organization (Sub-Admins).
	 * This is the inverse of the many-to-many relationship from User model.
	 */
	public function assignedUsers()
	{
		return $this->belongsToMany(User::class, 'user_organizations')->withTimestamps();
	}

	public function getLocalizedNameAttribute(): string
	{
		return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
	}

	/**
	 * Get the organization type that this organization belongs to.
	 */
	public function organizationType()
	{
		return $this->belongsTo(OrganizationTypes::class, 'organization_type_id');
	}

	/**
	 * Get the country for this organization.
	 */
	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	/**
	 * Get the state for this organization.
	 */
	public function state()
	{
		return $this->belongsTo(State::class);
	}

	/**
	 * Get the city for this organization.
	 */
	public function city()
	{
		return $this->belongsTo(City::class);
	}

	/**
	 * Get the industries (static categories) associated with this organization.
	 */
	public function industries()
	{
		return $this->belongsToMany(StaticCategory::class, 'organization_industries', 'organization_id', 'static_category_id')->withTimestamps();
	}

	// =============> Scopes <=================

	/**
	 * Scope to get only published (non-draft) organizations.
	 */
	public function scopePublished($query)
	{
		return $query->where('is_draft', false);
	}

	/**
	 * Scope to get only draft organizations.
	 */
	public function scopeDrafts($query)
	{
		return $query->where('is_draft', true);
	}

	/**
	 * Get the landing page for this organization.
	 */
	public function landingPage()
	{
		return $this->hasOne(OrganizationLandingPage::class);
	}

	/**
	 * Get the custom sections for this organization.
	 */
	public function customSections()
	{
		return $this->hasMany(OrganizationCustomSection::class)->orderBy('sort_order');
	}

	/**
	 * Check if a user is a participant of this organization.
	 *
	 * A user is considered a participant if ANY of these conditions are true:
	 * 1. User is assigned via user_organizations pivot table
	 * 2. User's organization_id field matches this organization
	 * 3. User is in a cycle belonging to this organization (User → Company → Step → Cycle → Organization)
	 *
	 * @param User|null $user
	 * @return bool
	 */
	public function isUserParticipant(?User $user): bool
	{
		if (!$user) {
			return false;
		}

		// Check 1: User assigned via pivot
		if ($this->assignedUsers()->where('users.id', $user->id)->exists()) {
			return true;
		}

		// Check 2: User's organization_id matches
		if ($user->organization_id === $this->id) {
			return true;
		}

		// Check 3: User is in a cycle belonging to this organization
		// User → Company → Step → Cycle → Organization
		if ($user->company && $user->company->step && $user->company->step->cycle) {
			if ($user->company->step->cycle->organization_id === $this->id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate a unique slug from organization name.
	 *
	 * @param string $name
	 * @param int|null $excludeId Organization ID to exclude from uniqueness check
	 * @return string
	 */
	public static function generateSlug($name, $excludeId = null)
	{
		$slug = Str::slug($name);
		$originalSlug = $slug;
		$counter = 1;

		while (static::where('slug', $slug)
			->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
			->exists()) {
			$slug = $originalSlug . '-' . $counter;
			$counter++;
		}

		return $slug;
	}

	/**
	 * Get the public landing page URL.
	 *
	 * @return string|null
	 */
	public function getLandingPageUrlAttribute()
	{
		if (!$this->slug) {
			return null;
		}

		return url('/o/' . $this->slug);
	}
}
