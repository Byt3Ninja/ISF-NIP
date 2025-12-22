<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
		'img_url'
	];
	public static function image_handler( $img )
    {
        if( !$img ) { return '/app-assets/images/logo_placeholder.png'; }
        return $img;
    }
	// ===========> Attributes <==============
	public function getNameAttribute($key)
	{

		return $this->attributes['name_' . app()->getLocale()];
	}
	public function getImageUrlAttribute()
	{
		$image = $this->img_url;
		return self::image_handler( $image );
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
}
