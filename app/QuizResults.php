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
	public static $module = 'Quiz Results';

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

	public static function refresh_my_attempts()
	{
		$user = auth()->user()->id;
		$my_pending_attempts = self::where('user_id', $user)->where('status', 0)->with(['quiz'])->get();
		foreach( $my_pending_attempts as $attempt )
		{
			$attempt_quiz = $attempt->quiz;
			if( !$attempt->status && $attempt->started_at->diffInMinutes(now()) > $attempt_quiz->time_limit )
			{
				$attempt->status = true;
				$attempt->ended_at = $attempt->started_at->copy()->addMinutes( $attempt_quiz->time_limit );
				$attempt->save();
			}
		}
	}
}
