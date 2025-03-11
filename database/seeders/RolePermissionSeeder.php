<?php

namespace Database\Seeders;

use App\Permission;
use App\Role;
use App\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonRole = File::get(base_path('database/dbFiles/roles.json'));
        $dataRole = json_decode($jsonRole,true);
        $jsonPermission = File::get(base_path('database/dbFiles/permissions.json'));
        $dataPermission = json_decode($jsonPermission,true);
        $jsonPermissionRole = File::get(base_path('database/dbFiles/role_has_permissions.json'));
        $dataPermissionRole = json_decode($jsonPermissionRole,true);

        foreach ($dataRole as $value){
            Role::create($value);
        }
        foreach ($dataPermission as $value){
            Permission::create($value);
        }
        foreach ($dataPermissionRole as $value) {
            DB::table('role_has_permissions')->insert([
                $value
            ]);
        }
        $user=User::first();
        $user->allotRole(Role::first()->name);
    }
}
