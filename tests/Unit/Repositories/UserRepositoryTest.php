<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\UserRepository;
use App\User;
use App\Role;
use App\Company;
use Illuminate\Support\Facades\Hash;

class UserRepositoryTest extends TestCase
{

    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    /** @test */
    public function it_can_get_base_query()
    {
        $query = $this->userRepository->baseQuery();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    /** @test */
    public function it_can_find_user_by_id()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->findById($user->id);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_user_not_found()
    {
        $result = $this->userRepository->findById(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_filter_users_by_gender()
    {
        User::factory()->create(['gender' => 'male']);
        User::factory()->create(['gender' => 'female']);

        $query = $this->userRepository->filter(['gender' => 'male']);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertEquals('male', $users->first()->gender);
    }

    /** @test */
    public function it_can_filter_users_by_company_status()
    {
        User::factory()->create(['HaveCompany' => 1]);
        User::factory()->create(['HaveCompany' => 0]);

        $query = $this->userRepository->filter(['haveCompany' => 1]);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertEquals(1, $users->first()->HaveCompany);
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $role = Role::factory()->create(['name' => 'Mentor']);
        $user = User::factory()->create();
        $user->assignRole('Mentor');

        $query = $this->userRepository->filter(['role' => 'Mentor']);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertTrue($users->first()->hasRole('Mentor'));
    }

    /** @test */
    public function it_can_search_users_by_name()
    {
        User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
        ]);
        User::factory()->create([
            'first_name_en' => 'Jane',
            'last_name_en' => 'Smith',
        ]);

        $query = $this->userRepository->filter(['search' => 'John']);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertStringContainsString('John', $users->first()->first_name_en);
    }

    /** @test */
    public function it_can_search_users_by_email()
    {
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@example.com']);

        $query = $this->userRepository->filter(['search' => 'john@example.com']);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertEquals('john@example.com', $users->first()->email);
    }

    /** @test */
    public function it_can_search_users_by_phone()
    {
        User::factory()->create(['phone' => '1234567890']);
        User::factory()->create(['phone' => '0987654321']);

        $query = $this->userRepository->filter(['search' => '1234567890']);
        $users = $query->get();

        $this->assertCount(1, $users);
        $this->assertEquals('1234567890', $users->first()->phone);
    }

    /** @test */
    public function it_can_paginate_users()
    {
        User::factory()->count(25)->create();

        $query = $this->userRepository->baseQuery();
        $paginated = $this->userRepository->paginate($query, 10, 1, 'desc');

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $paginated);
        $this->assertEquals(10, $paginated->count());
        $this->assertEquals(25, $paginated->total());
    }

    /** @test */
    public function it_can_create_user()
    {
        $userData = [
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890',
        ];

        $result = $this->userRepository->create($userData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(User::class, $result['data']);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name_en' => 'John',
        ]);
    }

    /** @test */
    public function it_can_update_user()
    {
        $user = User::factory()->create();
        $updateData = [
            'first_name_en' => 'Jane',
            'last_name_en' => 'Smith',
        ];

        $result = $this->userRepository->update($user, $updateData);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('Jane', $result->first_name_en);
        $this->assertEquals('Smith', $result->last_name_en);
    }

    /** @test */
    public function it_can_deactivate_user()
    {
        $user = User::factory()->create(['status' => 'active']);

        $result = $this->userRepository->deactivate($user);

        $this->assertTrue($result);
        $this->assertEquals('inactive', $user->fresh()->status);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->delete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_can_get_roles()
    {
        Role::factory()->count(5)->create();

        $result = $this->userRepository->roles();

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(5, $result['data']);
    }

    /** @test */
    public function it_can_update_password()
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;
        $newPassword = 'newpassword123';

        $result = $this->userRepository->updatePassword($user, [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNotEquals($oldPassword, $user->fresh()->password);
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }
}

