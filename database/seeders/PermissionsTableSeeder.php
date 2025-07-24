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
        $toUpdate = [];
        $getGroup = collect(DB::select("SELECT id, name FROM permission_groups"))->keyBy('name');
        $getPermissions = collect( DB::select("SELECT id, name, is_core, permission_group_id FROM permissions") )->keyBy('name');
        $json = File::get(base_path('database/dbFiles/_permissions.json'));
        $permissions = json_decode($json, true);
        if (empty($permissions)) {
            dd('فشل التحويل من JSON، أو الملف فارغ أو غير صالح', $json);
        }
        foreach( $permissions as $permission )
        {
            $groupName = $permission['permission_group_id'];
            $group = $getGroup->get( $groupName );
            if( !$group )
            {
                if( !empty( $groupName ) && trim( $groupName ) )
                {
                    // الجروب غير موجود، نضيفه
                    $newGroupId = DB::table('permission_groups')->insertGetId([
                        'name' => $groupName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $group = (object)['id' => $newGroupId, 'name' => $groupName];
                    $getGroup->put($groupName, $group);
                    $groupId = $group->id;
                } else { $groupId = null; }
            } else {
                $groupId = $group->id;
            }
            $found = $getPermissions->get($permission['name']);
            if( empty( $found ) ) { $toAdd[] = ['name' => $permission['name'], 'description' => $permission['description'], 'permission_group_id' => $groupId,  'guard_name' => 'web', 'is_core' => 0]; }
            else
            {
                if( empty( $found->permission_group_id ) )
                {
                    $toUpdate[] = ['id' => $found->id, 'permission_group_id' => $groupId];
                }
            }
        }
        foreach( $toUpdate as $entry )
        {
            $group = $getGroup->where('id', $entry['permission_group_id'])->first();
            $permission = $getPermissions->where('id', $entry['id'])->first();
            if( !empty( $permission ) && !empty( $group ) )
            {
                Permission::where('id', $entry['id'])->update(['permission_group_id' => $group->id]);
                $this->command->info("Permission group: {$group->name} attached successfully for: {$permission->name}");
            }
        }
        Permission::insert( $toAdd );
        foreach( $toAdd as $entry )
        {
            $name = $entry['name'];
            $this->command->info("Permission created successfully for: {$name}");
        }
    }
}
