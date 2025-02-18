<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Role extends Model
{
	use Auditable;
	protected $fillable = [
		'name',
		'default_for',
		'created_at',
		'updated_at',
	];
	public function permissions()
	{
		return $this->belongsToMany(Permission::class,'role_has_permissions');
	}

	public function _permissions()
	{
		return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
	}
}
