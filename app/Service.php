<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
	use Auditable;
	public static $module = 'Services';
	protected $fillable = ['organization_id', 'service_category_id', 'service_provider_id', 'name_en', 'name_ar','price', 'currency', 'status', 'avg_rating', 'description_en', 'description_ar', 'img_url','vendor_name','vendor_email','vendor_phone','hide_price','hide_vendor_info'];

	public function getNameAttribute()
	{
		return $this->{"name_".app()->getLocale()} ;
	}

	public function getDescriptionAttribute()
	{
		return $this->{"description_".app()->getLocale()} ;
	}

	public function provider()
	{
		return $this->belongsTo(serviceProviders::class, 'service_provider_id');
	}

	public function serviceCategory()
	{
		return $this->belongsTo(ServiceCategory::class,'service_category_id');
	}

	public function requests()
	{
		return $this->hasMany(ServicesRequests::class);
	}

	public function totalRate()
	{
		$rates = ServicesRequests::where('service_id', $this->id)->avg('rate');
		return ($rates??0) ;
	}

	public function myRate()
	{
		$rate = ServicesRequests::where('service_id', $this->id)->where('user_id',Auth::id())->avg('rate');
		return ($rate??0) ;
	}

	public function organizations()
	{
		return $this->belongsToMany(Organization::class, 'organization_service');
	}
}
