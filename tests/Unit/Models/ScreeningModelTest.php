<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Screening;
use App\Step;
use App\User;

class ScreeningModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $screening = new Screening();
        $fillable = $screening->getFillable();

        $this->assertContains('step_id', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('panel', $fillable);
        $this->assertContains('users', $fillable);
    }

    /** @test */
    public function it_casts_users_to_array()
    {
        $users = [1, 2, 3, 4, 5];
        $screening = Screening::factory()->create([
            'users' => json_encode($users),
        ]);

        $this->assertIsArray($screening->users);
        $this->assertEquals($users, $screening->users);
    }

    /** @test */
    public function it_can_belong_to_step()
    {
        $step = Step::factory()->create();
        $screening = Screening::factory()->create(['step_id' => $step->id]);

        $this->assertInstanceOf(Step::class, $screening->step);
        $this->assertEquals($step->id, $screening->step->id);
    }

    /** @test */
    public function it_can_belong_to_user()
    {
        $user = User::factory()->create();
        $screening = Screening::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $screening->user);
        $this->assertEquals($user->id, $screening->user->id);
    }
}

