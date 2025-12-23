<?php

use App\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('default_for', 25)->nullable()->default(null);
            $table->string('guard_name', 250)->default('web');
            $table->timestamps();
            $table->softDeletes();
        });

        $json = File::get(base_path('database/dbFiles/rolesWithDefaultPermissions.json'));
        $parsedFile = collect(json_decode($json, true));
        $roles = $parsedFile->flatMap(function ($role) {
            return collect($role['permissions'])->pluck('default_for')->flatten()->unique()->values();
        })->unique()->values();
        $rolesToCreate = [];
        foreach( $roles as $role )
        {
            $rolesToCreate[] = [
                'name' => $role,
                'default_for' => $role,
                'guard_name' => 'web',
            ];
        }
        if( count( $rolesToCreate ) ) { Role::insert( $rolesToCreate ); }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
