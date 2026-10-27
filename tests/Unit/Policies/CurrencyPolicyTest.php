<?php

namespace Tests\Unit\Policies;

use Tests\TestCase;
use App\Policies\CurrencyPolicy;
use App\User;
use App\Currency;

class CurrencyPolicyTest extends TestCase
{

    protected CurrencyPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CurrencyPolicy();
    }

    /** @test */
    public function it_allows_user_to_view_any_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('currencies.index');

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
        $user->givePermissionTo('currencies.create');

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_create_with_store_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('currencies.store');

        $result = $this->policy->create($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_if_has_permission()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $user->givePermissionTo('currencies.edit');

        $result = $this->policy->update($user, $currency);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_allows_user_to_update_with_update_permission()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $user->givePermissionTo('currencies.update');

        $result = $this->policy->update($user, $currency);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_update_if_no_permission()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();

        $result = $this->policy->update($user, $currency);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_user_to_delete_if_has_permission()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();
        $user->givePermissionTo('currencies.destroy');

        $result = $this->policy->delete($user, $currency);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_delete_if_no_permission()
    {
        $user = User::factory()->create();
        $currency = Currency::factory()->create();

        $result = $this->policy->delete($user, $currency);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_allows_user_to_update_rates_if_has_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('currencies.rates_update_all');

        $result = $this->policy->updateRates($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_denies_user_to_update_rates_if_no_permission()
    {
        $user = User::factory()->create();

        $result = $this->policy->updateRates($user);

        $this->assertFalse($result);
    }
}

