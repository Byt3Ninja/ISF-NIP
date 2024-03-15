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

	/**
	 * @return BelongsTo
	 **/
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
