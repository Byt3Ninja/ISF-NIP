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
		'description',
		'is_core',
		'permission_group_id',
		'created_at',
		'updated_at',
	];

	protected $casts = [
		'name' => 'string',
		'description' => 'string',
		'is_core' => 'boolean',
		'permission_group_id' => 'integer',
	];

	public function permissionsGroup()
	{
		return $this->belongsTo(permissionsGroup::class, 'permission_group_id');
	}

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
			'user.profile_view', 'user.profile_view', 'user.profile_update_basic', 'company.profile_view', 'company.profile_view_mine', 'company.profile_update_mine', 'assessments.results_analysis_view', 'calendar.events_list', 'messages.send', 'messages.folders_inbox', 'messages.folders_outbox', 'users.directory_view', 'users.view_companies', 
			'mentors.list', 'service_requests.list_mine', 'mentorships.list_mine', 'trainings.list_mine', 'quizzes.dashboard_view', 'mentorships.sessions_list_detailed', 'my_files.list', 'surveys.take', 'surveys.submit', 'cycles.list_mine', 'screening.access',
			'quizzes.view_overview_mine', 'mentorshipTasks.index', 'swot.view'
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
			'user.profile_view', 'user.profile_view', 'user.profile_update_basic', 'calendar.events_list', 'messages.send', 'messages.folders_inbox', 'messages.folders_outbox', 'users.view_companies', 'mentorships.sessions_list_detailed', 'mentorshipTasks.index', 'my_files.list'
		];
		$mentor_ids = $_permission->whereIn('name', $mentor_permissions)->pluck('id')->toArray();
		foreach( $mentors as $mentor )
		{
			$mentor->permissions()->sync( $mentor_ids );
		}
		$judgers = Role::where('default_for', 'judger')->get();
		if( empty( $judgers ) )
		{
			Role::create([
				'name' => 'Judger',
				'default_for' => 'judger',
			]);
			$judgers = Role::where('default_for', 'judger')->get();
		}
		$judger_permissions = [
			'user.profile_view', 'user.profile_view', 'user.profile_update_basic', 'calendar.events_list', 'messages.send', 'messages.folders_inbox', 'messages.folders_outbox', 'my_files.list', 'screening.access', 'screening.view', 'screening.form_view'
		];
		$judger_ids = $_permission->whereIn('name', $judger_permissions)->pluck('id')->toArray();
		foreach( $judgers as $judger )
		{
			$judger->permissions()->sync( $judger_ids );
		}
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
	}
	  public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
