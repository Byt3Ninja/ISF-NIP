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
        $getPermissions = collect( DB::select("SELECT id, name, is_core FROM permissions") );
        $json = File::get(base_path('database/dbFiles/_permissions.json'));
        $permissions = json_decode($json, true);
        foreach( $permissions as $permission )
        {
            $found = $getPermissions->where('name', $permission['name'])->first();
            if( empty( $found ) ) { $toAdd[] = ['name' => $permission['name'], 'guard_name' => 'web', 'is_core' => 0]; }
        }
        Permission::insert( $toAdd );

        foreach( $toAdd as $entry )
        {
            $name = $entry['name'];
            $this->command->info("Permission created successfully for: {$name}");
        }
    }
}
