<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluationResults extends Model
{
	use HasFactory;

	protected $table = 'screening_evaluation_results';
	protected $fillable = ['user_id', 'criteria_id', 'score', 'comment'];
	protected $casts = [
		'evaluation_id' => 'integer',
		'user_id' => 'integer',
		'criteria_id' => 'integer',
		'score' => 'double',
		'comment' => 'string',
		'status' => 'boolean'
	];

	public function evaluation()
	{
		return $this->belongsTo(ScreeningEvaluation::class, 'evaluation_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function criteria()
	{
		return $this->belongsTo(ScreeningEvaluationCriteria::class, 'criteria_id');
	}
}
