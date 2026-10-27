<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\CompanyRepository;
use App\Company;
use App\User;
use App\Country;
use App\State;
use App\City;

class CompanyRepositoryTest extends TestCase
{

    protected CompanyRepository $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyRepository = new CompanyRepository();
    }

    /** @test */
    public function it_can_find_company_by_id()
    {
        $company = Company::factory()->create();

        $result = $this->companyRepository->find($company->id);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertEquals($company->id, $result->id);
    }

    /** @test */
    public function it_can_find_company_with_relations()
    {
        $company = Company::factory()->create();
        $company->load(['user', 'country', 'state', 'city']);

        $result = $this->companyRepository->findWithRelations($company->id);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertTrue($result->relationLoaded('user'));
    }

    /** @test */
    public function it_can_create_company()
    {
        $user = User::factory()->create();
        $companyData = [
            'user_id' => $user->id,
            'name_en' => 'Test Company',
            'email' => 'company@example.com',
        ];

        $result = $this->companyRepository->create($companyData);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'name_en' => 'Test Company',
        ]);
    }

    /** @test */
    public function it_can_update_company()
    {
        $company = Company::factory()->create();
        $updateData = [
            'name_en' => 'Updated Company Name',
        ];

        $result = $this->companyRepository->update($company, $updateData);

        $this->assertTrue($result);
        $this->assertEquals('Updated Company Name', $company->fresh()->name_en);
    }

    /** @test */
    public function it_can_delete_company()
    {
        $company = Company::factory()->create();

        $result = $this->companyRepository->delete($company);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);
    }

    /** @test */
    public function it_can_get_companies_with_filters()
    {
        Company::factory()->count(5)->create(['sector' => 1]);
        Company::factory()->count(3)->create(['sector' => 2]);

        $filters = ['sector' => 1];
        $result = $this->companyRepository->getCompanies($filters, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    /** @test */
    public function it_can_create_founder()
    {
        $company = Company::factory()->create();
        $founderData = [
            'name' => 'John Founder',
            'position' => 'CEO',
            'email' => 'john@example.com',
        ];

        $result = $this->companyRepository->createFounder($company, $founderData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('team_members', [
            'Company_id' => $company->id,
            'TeamMember' => 'Founder',
        ]);
    }

    /** @test */
    public function it_can_create_milestone()
    {
        $company = Company::factory()->create();
        $milestoneData = [
            'title' => 'First Milestone',
            'description' => 'Description',
            'date' => now()->format('Y-m-d'),
        ];

        $result = $this->companyRepository->createMilestone($company, $milestoneData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('milestones', [
            'Company_id' => $company->id,
        ]);
    }
}

