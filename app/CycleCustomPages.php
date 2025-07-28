<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleCustomPages extends Model
{
	use HasFactory;

	protected $fillable = [
		'cycle_id',
		'title',
		'content',
		'is_active',
		'visibility',
		'row_no',
	];

	protected $casts = [
		'is_active' => 'boolean',
	];

	public function cycle()
	{
		return $this->belongsTo(Cycle::class, 'cycle_id');
	}
}
