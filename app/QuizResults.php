<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QuizResults extends Model
{
	use Auditable;
	public $timestamps = true;
	protected $table = 'quiz_results';
	protected $primaryKey = 'id';

	protected $fillable = [
		'quiz_id',
		'user_id',
		'score',
		'started_at',
		'ended_at',
		'status',
	];

	protected $casts = [
		'quiz_id' => 'integer',
		'user_id' => 'integer',
		'score' => 'double',
		'started_at' => 'datetime',
		'ended_at' => 'datetime',
		'status' => 'boolean',
	];

	public function quiz()
	{
		return $this->belongsTo(Quizzes::class, 'quiz_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function responses()
	{
		return $this->hasMany(QuizResponses::class, 'result_id');
	}
}
