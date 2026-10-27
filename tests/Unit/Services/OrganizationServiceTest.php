<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\OrganizationService;
use App\Repositories\OrganizationRepository;
use App\Organization;
use App\User;
use App\Role;
use App\Cycle;
use App\Company;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Mockery;

class OrganizationServiceTest extends TestCase
{

    protected OrganizationService $organizationService;
    protected $organizationRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organizationRepoMock = Mockery::mock(OrganizationRepository::class);
        $this->organizationService = new OrganizationService($this->organizationRepoMock);
        
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_list_organizations()
    {
        $organizations = Organization::factory()->count(5)->make();
        $counts = [
            'totalOrganizations' => 5,
            'totalActiveOrganizations' => 3,
            'totalInactiveOrganizations' => 2,
        ];

        $this->organizationRepoMock
            ->shouldReceive('allWithRelations')
            ->once()
            ->with(null)
            ->andReturn($organizations);

        $this->organizationRepoMock
            ->shouldReceive('counts')
            ->once()
            ->with(null)
            ->andReturn($counts);

        $result = $this->organizationService->listOrganizations();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('organizations', $result);
        $this->assertArrayHasKey('counts', $result);
        $this->assertEquals(5, $result['counts']['totalOrganizations']);
    }

    /** @test */
    public function it_can_list_organizations_by_status()
    {
        $activeOrganizations = Organization::factory()->count(3)->make(['status' => 1]);
        $counts = ['totalOrganizations' => 3, 'totalActiveOrganizations' => 3, 'totalInactiveOrganizations' => 0];

        $this->organizationRepoMock
            ->shouldReceive('allWithRelations')
            ->once()
            ->with(1)
            ->andReturn($activeOrganizations);

        $this->organizationRepoMock
            ->shouldReceive('counts')
            ->once()
            ->with(1)
            ->andReturn($counts);

        $result = $this->organizationService->listOrganizations(1);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['counts']['totalOrganizations']);
    }

    /** @test */
    public function it_can_create_organization_with_user_and_role()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('logo.jpg');
        $role = Role::factory()->create(['default_for' => 'organizationManager']);

        $data = [
            'firstName' => 'John',
            'lastName' => 'Manager',
            'email' => 'manager@example.com',
            'gender' => 'male',
            'phone' => '1234567890',
            'name' => 'Test Organization',
            'status' => 1,
            'password' => 'password123',
        ];

        // Service handles transactions internally, no need for manual transaction
        $result = $this->organizationService->createOrganization($data, $file, 'US');

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertDatabaseHas('organizations', [
            'name_en' => 'Test Organization',
            'status' => 1,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'manager@example.com',
            'first_name_en' => 'John',
        ]);
        
        $user = User::where('email', 'manager@example.com')->first();
        $this->assertTrue($user->hasRole('organizationManager'));
        $this->assertEquals($result->id, $user->organization_id);
        
        Mail::assertQueued(\App\Mail\OrganizationCreatedMail::class);
    }

    /** @test */
    public function it_can_create_organization_without_image()
    {
        $role = Role::factory()->create(['default_for' => 'organizationManager']);

        $data = [
            'firstName' => 'Jane',
            'lastName' => 'Admin',
            'email' => 'admin@example.com',
            'gender' => 'female',
            'phone' => '0987654321',
            'name' => 'Another Organization',
            'status' => 1,
            'password' => 'password123',
        ];

        // Service handles transactions internally
        $result = $this->organizationService->createOrganization($data, null, 'US');

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('/images/background/logo.png', $result->img_url);
    }

    /** @test */
    public function it_can_update_organization()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->organization_id = $organization->id;
        $user->save();
        $organization->update(['user_id' => $user->id]);

        $updateData = [
            'name' => 'Updated Organization',
            'firstName' => 'Updated',
            'lastName' => 'Name',
            'email' => 'updated@example.com',
            'status' => 1,
        ];

        // Service handles transactions internally
        $result = $this->organizationService->updateOrganization($organization, $updateData, null);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('Updated Organization', $result->name_en);
        $this->assertEquals('Updated', $user->fresh()->first_name_en);
    }

    /** @test */
    public function it_can_update_organization_with_new_image()
    {
        Storage::fake('public');

        $organization = Organization::factory()->create(['img_url' => 'old-logo.jpg']);
        $user = User::factory()->create();
        $user->organization_id = $organization->id;
        $user->save();
        $organization->update(['user_id' => $user->id]);
        
        $file = UploadedFile::fake()->image('new-logo.jpg');

        $updateData = [
            'name' => 'Updated Organization',
        ];

        // Service handles transactions internally
        $result = $this->organizationService->updateOrganization($organization, $updateData, $file);

        $this->assertNotEquals('old-logo.jpg', $result->img_url);
        $this->assertStringContainsString('organizations/', $result->img_url);
    }

    /** @test */
    public function it_can_update_organization_password()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'organization_id' => $organization->id,
            'password' => bcrypt('oldpassword'),
        ]);
        $organization->update(['user_id' => $user->id]);
        
        $oldPassword = $user->password;

        $updateData = [
            'password' => 'newpassword123',
        ];

        // Service handles transactions internally
        $result = $this->organizationService->updateOrganization($organization, $updateData, null);

        $this->assertNotEquals($oldPassword, $user->fresh()->password);
        $this->assertTrue(\Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function it_can_soft_delete_organization_with_cycles()
    {
        $organization = Organization::factory()->create();
        $cycle = Cycle::factory()->create(['organization_id' => $organization->id]);

        $result = $this->organizationService->deleteOrganization($organization);

        $this->assertEquals('soft', $result);
        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
    }

    /** @test */
    public function it_can_soft_delete_organization_with_companies()
    {
        $organization = Organization::factory()->create();
        $cycle = Cycle::factory()->create(['organization_id' => $organization->id]);
        $company = Company::factory()->create(['cycle' => $cycle->id]);

        $result = $this->organizationService->deleteOrganization($organization);

        $this->assertEquals('soft', $result);
        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
    }

    /** @test */
    public function it_can_force_delete_organization_without_dependencies()
    {
        $organization = Organization::factory()->create();

        $result = $this->organizationService->deleteOrganization($organization);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }

    /** @test */
    public function it_uploads_image_correctly()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test-logo.png');
        
        $reflection = new \ReflectionClass($this->organizationService);
        $method = $reflection->getMethod('uploadImage');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->organizationService, $file);

        $this->assertStringContainsString('organizations/', $result);
        $this->assertStringContainsString('.png', $result);
    }

    /** @test */
    public function it_returns_default_image_when_no_file_provided()
    {
        $reflection = new \ReflectionClass($this->organizationService);
        $method = $reflection->getMethod('uploadImage');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->organizationService, null);

        $this->assertEquals('/images/background/logo.png', $result);
    }
}

