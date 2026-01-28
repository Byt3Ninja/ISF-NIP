<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class serviceProviders extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'service_providers';
	public static $module = 'Service Providers';
	protected $primaryKey = 'id';

	protected $fillable = [
		'company_name',
		'contact_name',
		'email',
		'phone',
		'status',
		'user_id',
		'whatsapp',
		'country_code_w_a',
		'preferred_language',
		'country_id',
		'state_id',
		'city_id',
		'address',
		'zip_code',
		'description',
		'img_url',
	];

	protected $casts = [
		'company_name' => 'string',
		'contact_name' => 'string',
		'email' => 'string',
		'country_code' => 'string',
		'phone' => 'string',
		'status' => 'boolean',
		'user_id' => 'integer',
		'country_id' => 'integer',
		'state_id' => 'integer',
		'city_id' => 'integer',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
