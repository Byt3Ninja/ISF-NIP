<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class serviceProviderRegistration extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'service_provider_registration';
	public static $module = 'Service Provider Registration Requests';
	protected $primaryKey = 'id';

	protected $fillable = [
		'company_name',
		'contact_name',
		'email',
		'country_code',
		'phone',
		'status',
		'user_id',
		'action_by',
		'action_at',
		'reason',
		'ip_address',
		'locale',
	];

	protected $casts = [
		'company_name' => 'string',
		'contact_name' => 'string',
		'email' => 'string',
		'country_code' => 'string',
		'phone' => 'string',
		'status' => 'boolean',
		'user_id' => 'integer',
		'action_by' => 'integer',
		'action_at' => 'datetime',
		'reason' => 'string',
		'ip_address' => 'string',
		'locale' => 'string',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
