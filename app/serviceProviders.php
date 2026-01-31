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
		'hero_image',
		'hero_description',
		'services_short_description',
		'appointment_link',
		'operating_mode',
		'operating_region',
        'show_stats',
        'stat1_label', 'stat1_value',
        'stat2_label', 'stat2_value',
        'stat3_label', 'stat3_value',
        'stat4_label', 'stat4_value',
        'show_portfolio',
        'portfolio_description',
    ];

	protected $casts = [
		'company_name' => 'string',
		'contact_name' => 'string',
		'email' => 'string',
		'country_code' => 'string',
		'phone' => 'string',
		'status' => 'boolean',
        'show_stats' => 'boolean',
        'show_portfolio' => 'boolean',
        'portfolio_description' => 'string',
		'user_id' => 'integer',
		'country_id' => 'integer',
		'state_id' => 'integer',
		'city_id' => 'integer',
		'hero_description' => 'string',
		'services_short_description' => 'string',
		'appointment_link' => 'string',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

    public function portfolios()
    {
        return $this->hasMany(ServiceProviderPortfolio::class, 'service_provider_id')->orderBy('order');
    }
}
