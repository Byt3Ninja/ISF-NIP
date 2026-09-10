<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Role extends Model
{
	use HasFactory, Auditable;
	protected $fillable = [
		'name',
		'default_for',
		'created_at',
		'updated_at',
	];
	public static $module = 'Roles';
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

	public static function _users( $role )
	{
		$role = strtolower($role);
		$get_role = Role::where('default_for', $role)->with(['users', 'users.roles'])->get();
		$get_role = $get_role->flatMap(function ($r) {
			return $r->users;
		})->unique('id')->values();
		return $get_role;
	}
}
