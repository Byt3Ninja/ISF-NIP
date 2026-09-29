<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
	protected $table = 'service_categories';
	protected $fillable = ['id', 'organization_id', 'created_by', 'name_en', 'name_ar'];

	public function getNameAttribute()
	{
		return $this->{"name_".app()->getLocale()} ;
	}

	public function services()
	{
		return $this->hasMany(Service::class, 'service_category_id');
	}

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}

	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}
}
