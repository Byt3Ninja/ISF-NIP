<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QuestionsBank extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'questions_bank';
	protected $primaryKey = 'id';

	protected $fillable = [
		'organization_id',
		'category_id',
		'question',
		'type',
		'correct',
		'answers',
		'explanation',
		'difficulty',
		'tags',
	];

	protected $casts = [
		'organization_id' => 'integer',
		'category_id' => 'integer',
		'question' => 'string',
		'type' => 'string',
		'correct' => 'boolean',
		'answers' => 'collection',
		'explanation' => 'string',
		'difficulty' => 'string',
		'tags' => 'collection',
	];

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}

	public function category()
	{
		return $this->belongsTo(QuestionCategories::class, 'category_id');
	}
}
