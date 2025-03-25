<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QuestionCategories extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'question_categories';
	public static $module = 'Question Categories';
	protected $primaryKey = 'id';

	protected $fillable = [
		'organization_id',
		'title',
	];

	protected $casts = [
		'organization_id' => 'integer',
		'title' => 'string',
	];

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}

	public function questions()
	{
		return $this->hasMany(QuestionsBank::class, 'category_id');
	}
}
