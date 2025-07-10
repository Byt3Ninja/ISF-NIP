<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class permissionsGroup extends Model
{
	use Auditable;
	protected $primaryKey = 'id';
	protected $table = 'permission_groups';
	public static $module = 'Permission Groups';

	protected $fillable = [
		'name',
		'description',
	];

	protected $casts = [
		'name' => 'string',
		'description' => 'string',
	];

	public function permissions()
	{
		return $this->hasMany(Permission::class, 'permission_group_id');
	}
}
