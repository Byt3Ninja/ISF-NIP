<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluation extends Model
{
	use HasFactory;

	protected $table = 'screening_evaluation';
	protected $fillable = ['step_id', 'status'];
	protected $casts = [
		'step_id' => 'integer',
		'status' => 'boolean',
	];

	public function step()
	{
		return $this->belongsTo(Step::class);
	}

	public function criteria()
	{
		return $this->hasMany(ScreeningEvaluationCriteria::class, 'evaluation_id');
	}
}
