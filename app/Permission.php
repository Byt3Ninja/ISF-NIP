<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Permission extends Model
{
	use Auditable;
	protected $table = 'permissions';
	public static $module = 'Permissions';

	protected $fillable = [
		'name',
		'created_at',
		'updated_at',
	];

	public static function default_permissions()
	{
		$json = \File::get(base_path('database/dbFiles/_permissions.json'));
		$permissions = json_decode($json, true);
		$Permission = self::where('id', '>', 0);
		$_permission = $Permission->get();
		$ids = $_permission->pluck('id')->toArray();
		RolePermissions::whereIn('permission_id', $ids)->delete();
		$Permission->delete();
		self::insert($permissions);
		$admins = Role::where('default_for', 'admin')->get();
		if( empty( $admins ) )
		{
			Role::create([
				'name' => 'Admin',
				'default_for' => 'admin',
			]);
			$admins = Role::where('default_for', 'admin')->get();
		}
		$_permission = self::get();
		$ids = $_permission->pluck('id')->toArray();
		foreach( $admins as $admin )
		{
			$admin->permissions()->sync( $ids );
		}
		$startups = Role::where('default_for', 'registered')->get();
		if( empty( $startups ) )
		{
			Role::create([
				'name' => 'Registered',
				'default_for' => 'registered',
			]);
			$startups = Role::where('default_for', 'registered')->get();
		}
		$startup_permissions = [
			'show_my_profile', 'my_profile', 'update_my_profile', 'company_profile', 'my_company', 'update_my_company', 'analysis-assessment', 'calendar', 'sendmessage', 'inbox', 'outbox', 'directory', 'users.companies', 
			'mentors.list', 'myrequests', 'my.mentorships', 'training.my', 'quizzes.dashboard', 'mentorshipSessions.index', 'myFiles.index', 'surveys.taking', 'surveys.submit', 'cycles.my', 'screening',
			'quiz.overview', 'mentorshipTasks.index', 'swot.view'
		];
		$startup_ids = $_permission->whereIn('name', $startup_permissions)->pluck('id')->toArray();
		foreach( $startups as $startup )
		{
			$startup->permissions()->sync( $startup_ids );
		}
		$mentors = Role::where('default_for', 'mentor')->get();
		if( empty( $mentors ) )
		{
			Role::create([
				'name' => 'Mentor',
				'default_for' => 'mentor',
			]);
			$mentors = Role::where('default_for', 'mentor')->get();
		}
		$mentor_permissions = [
			'show_my_profile', 'my_profile', 'update_my_profile', 'calendar', 'sendmessage', 'inbox', 'outbox', 'users.companies', 'mentorshipSessions.index', 'mentorshipTasks.index', 'myFiles.index'
		];
		$mentor_ids = $_permission->whereIn('name', $mentor_permissions)->pluck('id')->toArray();
		foreach( $mentors as $mentor )
		{
			$mentor->permissions()->sync( $mentor_ids );
		}
		$judgers = Role::where('default_for', 'judger')->get();
		if( empty( $judger ) )
		{
			Role::create([
				'name' => 'Judger',
				'default_for' => 'judger',
			]);
			$judgers = Role::where('default_for', 'judger')->get();
		}
		$judger_permissions = [
			'show_my_profile', 'my_profile', 'update_my_profile', 'calendar', 'sendmessage', 'inbox', 'outbox', 'myFiles.index', 'screening', 'screening.view', 'screening.openForm'
		];
		$judger_ids = $_permission->whereIn('name', $judger_permissions)->pluck('id')->toArray();
		foreach( $judgers as $judger )
		{
			$judger->permissions()->sync( $judger_ids );
		}
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
	}
}
