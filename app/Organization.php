<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Organization extends Model
{
	use Auditable,SoftDeletes;
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
		'api_token',
		'created_at',
		'updated_at',
		'img_url',
		'organization_type_id',
		'is_draft',
		'wizard_step',
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
		'linkedin_company_url',
		'twitter_handle',
		'facebook_page_url',
		'instagram_profile_url',
		'youtube_channel_url',
		'tiktok_url',
	];

	protected $casts = [
		'is_draft' => 'boolean',
		'status' => 'boolean',
	];
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

		// Check if it's an old path (starts with 'organizations/')
		if (str_starts_with($image, 'organizations/')) {
			return asset($image);
		}

		// Check if it's a full URL or starts with /
		if (str_starts_with($image, 'http') || str_starts_with($image, '/')) {
			return $image;
		}

		// New storage path - return route to serve from storage
		return route('organizations.logo', $image);
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
}
