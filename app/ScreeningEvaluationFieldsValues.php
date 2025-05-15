<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningEvaluationFieldsValues extends Model
{
	use HasFactory;

	protected $table = 'screening_evaluation_fields_values';
	protected $fillable = ['custom_field_id', 'value', 'user_id'];
	protected $casts = [
		'value' => 'string',
		'custom_field_id' => 'integer',
		'user_id' => 'integer',
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function field()
	{
		return $this->belongsTo(ScreeningEvaluationCustomFields::class, 'custom_field_id');
	}
}
