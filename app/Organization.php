<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
	use Auditable;
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
	];
    // ===========> Attributes <==============
    public function getNameAttribute($key){

        return $this->attributes['name_'.app()->getLocale()];
    }
    public function getImageUrlAttribute()
    {
        $image = $this->img_url;
        if (!$image) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return "/191014/".$image;
    }
    // =============> Relations <=================
	/**
	 * @return BelongsTo
	 **/

    public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

}
