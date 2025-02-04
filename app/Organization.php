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
	public function getNameAttribute($key)
	{

		return $this->attributes['name_' . app()->getLocale()];
	}
	public function getImageUrlAttribute()
	{
		$image = $this->img_url;
		if (!$image) {
			return '/app-assets/images/logo_placeholder.png';
		}
		return $image;
	}
	// =============> Relations <=================
	/**
	 * @return BelongsTo
	 **/

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
}
