<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluationCriteria extends Model
{
	use HasFactory;
	
	protected $table = 'screening_evaluation_criteria';
	protected $fillable = ['title', 'weight', 'result', 'evaluation_id', 'parent_id'];
	protected $casts = [
		'title' => 'string',
		'weight' => 'double',
		'result' => 'string',
		'evaluation_id' => 'integer',
		'parent_id' => 'integer',
	];

	public function evaluation()
	{
		return $this->belongsTo(ScreeningEvaluation::class, 'evaluation_id');
	}

	public function parent()
	{
		return $this->belongsTo(ScreeningEvaluationCriteria::class, 'parent_id');
	}
}
