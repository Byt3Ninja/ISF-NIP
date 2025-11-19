<?php

namespace App;

use stdClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDataJudgesComments extends Model
{
	use HasFactory;

	protected $table = 'form_data_judges_comments';
	protected $fillable = ['form_data_id', 'user_id', 'comment'];
	protected $casts = [
		'form_data_id' => 'integer',
		'user_id' => 'integer',
		'comment' => 'string'
	];

	public function formData()
	{
		return $this->belongsTo(Formdata::class, 'form_data_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
