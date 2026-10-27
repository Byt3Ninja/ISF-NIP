<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Policies\OrganizationPolicy;
use App\User;
use App\Organization;

class OrganizationPolicyTest extends TestCase
{

    protected OrganizationPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrganizationPolicy();
    }

    /** @test */
    public function it_allows_user_to_view_any_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('organizations.index');

        $result = $this->policy->viewAny($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_view_any_if_no_permission()
    {
        $user = User::factory()->create();

        $result = $this->policy->viewAny($user);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_user_to_create_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('organizations.create');

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_create_with_store_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('organizations.store');

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_if_has_permission()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->givePermissionTo('organizations.edit');

        $result = $this->policy->update($user, $organization);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_with_update_permission()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->givePermissionTo('organizations.update');

        $result = $this->policy->update($user, $organization);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_update_if_no_permission()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $result = $this->policy->update($user, $organization);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_user_to_delete_if_has_permission()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->givePermissionTo('organizations.destroy');

        $result = $this->policy->delete($user, $organization);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_delete_if_no_permission()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $result = $this->policy->delete($user, $organization);

        $this->assertFalse($result);
    }
}

