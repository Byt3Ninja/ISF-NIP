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
		'group',
		'question',
		'type',
		'survey_type',
		'correct',
		'answers',
		'explanation',
		'difficulty',
		'tags',
		'explanation_QA_ar',
		'explanation_QA_en',
		'explanation_AA_ar',
		'explanation_AA_en',
		'ascategory_id',
		'subcategory_id',
	];

	protected $casts = [
		'organization_id' => 'array',
		'category_id' => 'integer',
		'group' => 'string',
		'question' => 'string',
		'type' => 'string',
		'survey_type' => 'string',
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

	public function ascategory()
	{
		return $this->belongsTo(Category::class, 'ascategory_id');
	}
	public function subcategory()
	{
		return $this->belongsTo(SubCategory::class, 'subcategory_id');
	}
}
