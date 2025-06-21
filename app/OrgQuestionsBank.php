<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class OrgQuestionsBank extends Model
{
	use Auditable;
	public static $module = 'organization questions bank';
	protected $table = 'organization_question_bank';
	protected $fillable = [
		'organization_id',
		'question_id',
	];

	public function question()
	{
		return $this->belongsTo(QuestionsBank::class, 'question_id');
	}

	public function organization()
	{
		return $this->belongsTo(Organization::class, 'organization_id');
	}
}
