<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CompanyService;
use App\Repositories\CompanyRepository;
use App\Company;
use App\User;
use App\Team_member;
use App\Milestone;
use Illuminate\Support\Facades\Storage;
use Mockery;

class CompanyServiceTest extends TestCase
{

    protected CompanyService $companyService;
    protected $companyRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->companyRepositoryMock = Mockery::mock(CompanyRepository::class);
        $this->companyService = new CompanyService($this->companyRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_companies_with_filters()
    {
        $filters = ['search' => 'test', 'sector' => 1];
        $expectedResult = ['data' => collect([])];

        $this->companyRepositoryMock
            ->shouldReceive('getCompanies')
            ->once()
            ->with($filters, true)
            ->andReturn($expectedResult);

        $result = $this->companyService->getCompanies($filters, true);

        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_can_get_company_profile_for_authorized_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('company.profile_view');
        $company = Company::factory()->create();

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->with($company->id)
            ->andReturn($company);

        $result = $this->companyService->getCompanyProfile($company->id, $user);

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['type']);
        $this->assertArrayHasKey('data', $result);
    }

    /** @test */
    public function it_returns_forbidden_for_unauthorized_user()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->with($company->id)
            ->andReturn($company);

        $result = $this->companyService->getCompanyProfile($company->id, $user);

        $this->assertIsArray($result);
        $this->assertEquals('forbidden', $result['type']);
    }

    /** @test */
    public function it_returns_not_found_when_company_does_not_exist()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('company.profile_view');

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $result = $this->companyService->getCompanyProfile(99999, $user);

        $this->assertIsArray($result);
        $this->assertEquals('not_found', $result['type']);
    }

    /** @test */
    public function it_allows_company_owner_to_view_their_company()
    {
        $user = User::factory()->create();
        $user->assignRole('Registered');
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->with($company->id)
            ->andReturn($company);

        $result = $this->companyService->getCompanyProfile($company->id, $user);

        $this->assertEquals('success', $result['type']);
    }

    /** @test */
    public function it_can_update_company_profile()
    {
        Storage::fake('public_uploads');
        Storage::fake('pitch_deck');
        
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $updateData = [
            'name' => 'Updated Company Name',
            'address' => 'New Address',
            'country_id' => 1,
            'stage_id' => 1,
        ];

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->with($company->id)
            ->andReturn($company);

        $this->companyRepositoryMock
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Company::class));

        $result = $this->companyService->updateCompanyProfile($updateData, $user);

        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('company', $result);
    }

    /** @test */
    public function it_can_create_founder()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $founderData = [
            'name' => 'John Founder',
            'position' => 'CEO',
            'email' => 'john@example.com',
        ];

        $this->companyRepositoryMock
            ->shouldReceive('createFounder')
            ->once()
            ->with($company, $founderData)
            ->andReturn(['success' => true]);

        $result = $this->companyService->createFounder($founderData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_update_founder()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);
        $founder = Team_member::factory()->create([
            'Company_id' => $company->id,
            'TeamMember' => 'Founder',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'position' => 'CTO',
        ];

        $this->companyRepositoryMock
            ->shouldReceive('updateFounder')
            ->once()
            ->with(Mockery::type(Team_member::class), $updateData)
            ->andReturn(['success' => true]);

        $result = $this->companyService->updateFounder($founder->id, $updateData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_create_milestone()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $milestoneData = [
            'title' => 'First Milestone',
            'description' => 'Description',
            'date' => now()->format('Y-m-d'),
        ];

        $this->companyRepositoryMock
            ->shouldReceive('createMilestone')
            ->once()
            ->with($company, $milestoneData)
            ->andReturn(['success' => true]);

        $result = $this->companyService->createMilestone($milestoneData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_delete_founder()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $this->companyRepositoryMock
            ->shouldReceive('deleteFounder')
            ->once()
            ->with($company->id, 1)
            ->andReturn(true);

        $result = $this->companyService->deleteFounder($company->id, 1);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_file_uploads_in_company_update()
    {
        Storage::fake('public_uploads');
        Storage::fake('pitch_deck');
        
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->update(['company_id' => $company->id]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('logo.jpg', 100);
        
        $updateData = [
            'logo' => $file,
            'name' => 'Test Company',
        ];

        $this->companyRepositoryMock
            ->shouldReceive('findWithRelations')
            ->once()
            ->andReturn($company);

        $this->companyRepositoryMock
            ->shouldReceive('save')
            ->once();

        $result = $this->companyService->updateCompanyProfile($updateData, $user);

        $this->assertEquals('success', $result['status']);
    }
}

