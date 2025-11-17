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
		'phone',
		'status',
		'user_id',
		'ip_address',
	];

	protected $casts = [
		'company_name' => 'string',
		'contact_name' => 'integer',
		'email' => 'string',
		'phone' => 'string',
		'status' => 'boolean',
		'user_id' => 'integer',
		'ip_address' => 'string',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
