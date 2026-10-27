<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Role;
use App\User;
use App\Permission;

class RoleModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $role = new Role();
        $fillable = $role->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('default_for', $fillable);
    }

    /** @test */
    public function it_can_have_permissions()
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'users.index']);

        $role->givePermissionTo('users.index');

        $this->assertTrue($role->hasPermissionTo('users.index'));
        $this->assertCount(1, $role->permissions);
    }

    /** @test */
    public function it_can_revoke_permissions()
    {
        $role = Role::factory()->create();
        $role->givePermissionTo('users.index');
        $role->givePermissionTo('users.create');

        $role->revokePermissionTo('users.index');

        $this->assertFalse($role->hasPermissionTo('users.index'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
        $this->assertCount(1, $role->permissions);
    }

    /** @test */
    public function it_can_have_users()
    {
        $role = Role::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->assignRole($role->name);
        $user2->assignRole($role->name);

        $this->assertCount(2, $role->users);
        $this->assertTrue($user1->hasRole($role->name));
        $this->assertTrue($user2->hasRole($role->name));
    }

    /** @test */
    public function it_can_check_if_has_permission()
    {
        $role = Role::factory()->create();
        $role->givePermissionTo('users.index');
        $role->givePermissionTo('users.create');

        $this->assertTrue($role->hasPermissionTo('users.index'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
        $this->assertFalse($role->hasPermissionTo('users.delete'));
    }

    /** @test */
    public function it_has_permissions_relationship()
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'users.index']);

        $role->givePermissionTo('users.index');

        $this->assertCount(1, $role->permissions);
        $this->assertCount(1, $role->_permissions);
    }

    /** @test */
    public function it_can_sync_permissions()
    {
        $role = Role::factory()->create();
        $permission1 = Permission::factory()->create(['name' => 'users.index']);
        $permission2 = Permission::factory()->create(['name' => 'users.create']);
        $permission3 = Permission::factory()->create(['name' => 'users.update']);

        $role->syncPermissions(['users.index', 'users.create']);

        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('users.index'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
        $this->assertFalse($role->hasPermissionTo('users.update'));
    }
}

