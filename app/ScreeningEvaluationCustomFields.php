<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluationCustomFields extends Model
{
	use HasFactory;

	protected $table = 'screening_evaluation_custom_fields';
	protected $fillable = ['name', 'evaluation_id'];
	protected $casts = [
		'name' => 'string',
		'evaluation_id' => 'integer',
	];

	public function evaluation()
	{
		return $this->belongsTo(ScreeningEvaluation::class, 'evaluation_id');
	}

	public function values()
	{
		return $this->hasMany(ScreeningEvaluationFieldsValues::class, 'custom_field_id');
	}
}
