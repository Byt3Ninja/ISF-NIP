<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Policies\UserPolicy;
use App\User;
use App\Role;

class UserPolicyTest extends TestCase
{

    protected UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    /** @test */
    public function it_allows_user_to_view_index_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.index');

        $result = $this->policy->index($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_view_index_if_no_permission()
    {
        $user = User::factory()->create();

        $result = $this->policy->index($user);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_user_to_view_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.show');

        $result = $this->policy->view($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_create_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.create');

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_store_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.store');

        $result = $this->policy->store($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_if_has_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo('users.edit');

        $result = $this->policy->update($user, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_with_update_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo('users.update');

        $result = $this->policy->update($user, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_deactivate_if_has_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo('users.edit');

        $result = $this->policy->deactivate($user, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_password_if_has_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo('users.update');

        $result = $this->policy->updatePassword($user, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_own_password()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('user.profile_update_my_password');

        $result = $this->policy->updateMyPassword($user, $user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_destroy_if_has_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        $user->givePermissionTo('users.destroy');

        $result = $this->policy->destroy($user, $targetUser);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_destroy_if_no_permission()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $result = $this->policy->destroy($user, $targetUser);

        $this->assertFalse($result);
    }
}

