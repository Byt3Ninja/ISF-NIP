<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create Sub-Admin role
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Sub-Admin',
            'default_for' => 'subadmin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Copy permissions from organizationManager role
        $orgManagerRole = DB::table('roles')
            ->where('default_for', 'organizationmanager')
            ->first();

        if ($orgManagerRole) {
            $permissions = DB::table('role_has_permissions')
                ->where('role_id', $orgManagerRole->id)
                ->get();

            foreach ($permissions as $permission) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permission->permission_id,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = DB::table('roles')
            ->where('default_for', 'subadmin')
            ->first();

        if ($role) {
            // Remove permissions
            DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->delete();

            // Remove role
            DB::table('roles')
                ->where('id', $role->id)
                ->delete();
        }
    }
};
