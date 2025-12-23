<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationTypes extends Model
{
	use Auditable, SoftDeletes;
	protected $table = 'organization_types';
	public static $module = 'Organization Types';
	protected $fillable = [
		'title_en',
		'title_ar',
	];

	// ===========> Attributes <==============
	public function getNameAttribute($key)
	{
		return $this->attributes['title_' . app()->getLocale()];
	}

	public function getLocalizedNameAttribute(): string
	{
		return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
	}

	// =============> Relations <=================

	/**
	 * Get the organizations that belong to this type.
	 */
	public function organizations()
	{
		return $this->hasMany(Organization::class, 'organization_type_id');
	}
}
