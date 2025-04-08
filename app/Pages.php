<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Pages extends Model
{
	use Auditable;
	public static $module = 'Pages';
	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
