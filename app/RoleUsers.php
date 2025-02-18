<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleUsers extends Model {
	protected $table = 'model_has_roles';
	public $timestamps = false;
	protected $fillable = [
		'role_id',
		'model_type',
		'model_id',
	];

	protected $casts = [
		'role_id' => 'integer',
		'model_id' => 'integer',
		'model_type' => 'string',
	];

	/**
	 * @return BelongsTo
	 **/
	public function user()
	{
		return $this->belongsTo(User::class, 'model_id', 'id');
	}

	/**
	 * @return BelongsTo
	 **/
	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id', 'id');
	}
}