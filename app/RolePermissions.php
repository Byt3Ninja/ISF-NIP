<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model {
	protected $table = 'role_has_permissions';
	public $timestamps = false;
	protected $fillable = [
		'permission_id',
		'role_id',
	];

	protected $casts = [
		'permission_id' => 'integer',
		'role_id' => 'integer',
	];

	/**
	 * @return BelongsTo
	 **/
	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id', 'id');
	}

	/**
	 * @return BelongsTo
	 **/
	public function permission()
	{
		return $this->belongsTo(Permission::class, 'permission_id', 'id');
	}
}