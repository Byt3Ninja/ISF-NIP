<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Permission;
use File;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $toAdd = [];
        $getgroup = collect(DB::select("SELECT id, name FROM permission_groups"))->keyBy('name');
        $getPermissions = collect( DB::select("SELECT id, name, is_core FROM permissions") )->keyBy('name');
        $json = File::get(base_path('database/dbFiles/_permissions.json'));
        $permissions = json_decode($json, true);

        foreach( $permissions as $permission )
        {
            $groupName = $permission['permission_group_id'];
            $group = $getgroup->get($groupName);
            if (!$group) {
                // الجروب غير موجود، نضيفه
                $newGroupId = DB::table('permission_groups')->insertGetId([
                    'name' => $groupName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $group = (object)['id' => $newGroupId, 'name' => $groupName];
                $getgroup->put($groupName, $group);
                $groupId = $group->id;
            } else {
                $groupId = $group->id;
            }
            $found = $getPermissions->get($permission['name']);
            if( empty( $found ) ) { $toAdd[] = ['name' => $permission['name'], 'description' => $permission['description'], 'permission_group_id' => $groupId,  'guard_name' => 'web', 'is_core' => 0]; }
        }
        Permission::insert( $toAdd );

        foreach( $toAdd as $entry )
        {
            $name = $entry['name'];
            $this->command->info("Permission created successfully for: {$name}");
        }
    }
}
