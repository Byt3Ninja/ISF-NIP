<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\OrganizationRepository;
use App\Organization;
use App\User;
use App\Cycle;
use App\Company;

class OrganizationRepositoryTest extends TestCase
{

    protected OrganizationRepository $organizationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationRepository = new OrganizationRepository();
    }

    /** @test */
    public function it_can_get_all_organizations_with_relations()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->organization_id = $organization->id;
        $user->save();
        $cycle = Cycle::factory()->create(['organization_id' => $organization->id]);

        $result = $this->organizationRepository->allWithRelations();

        $this->assertIsIterable($result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_can_get_organizations_by_status()
    {
        Organization::factory()->create(['status' => 1]);
        Organization::factory()->create(['status' => 0]);

        $result = $this->organizationRepository->allWithRelations(1);

        $this->assertIsIterable($result);
        $this->assertEquals(1, $result->count());
        $this->assertEquals(1, $result->first()->status);
    }

    /** @test */
    public function it_can_get_organization_counts()
    {
        Organization::factory()->count(3)->create(['status' => 1]);
        Organization::factory()->count(2)->create(['status' => 0]);

        $result = $this->organizationRepository->counts();

        $this->assertIsArray($result);
        $this->assertEquals(5, $result['totalOrganizations']);
        $this->assertEquals(3, $result['totalActiveOrganizations']);
        $this->assertEquals(2, $result['totalInactiveOrganizations']);
    }

    /** @test */
    public function it_can_get_organization_counts_by_status()
    {
        Organization::factory()->count(3)->create(['status' => 1]);

        $result = $this->organizationRepository->counts(1);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['totalOrganizations']);
        $this->assertEquals(3, $result['totalActiveOrganizations']);
        $this->assertEquals(0, $result['totalInactiveOrganizations']);
    }

    /** @test */
    public function it_can_find_organization_by_id()
    {
        $organization = Organization::factory()->create();

        $result = $this->organizationRepository->find($organization->id);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals($organization->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_organization_not_found()
    {
        $result = $this->organizationRepository->find(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_find_organization_with_cycles()
    {
        $organization = Organization::factory()->create();
        $cycle1 = Cycle::factory()->create(['organization_id' => $organization->id]);
        $cycle2 = Cycle::factory()->create(['organization_id' => $organization->id]);
        $company = Company::factory()->create(['cycle' => $cycle1->id]);

        $result = $this->organizationRepository->findWithCycles($organization->id);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertTrue($result->relationLoaded('cycles'));
        $this->assertCount(2, $result->cycles);
        $this->assertTrue($result->cycles->first()->relationLoaded('companies'));
    }

    /** @test */
    public function it_can_create_organization()
    {
        $data = [
            'name_en' => 'Test Organization',
            'name_ar' => 'منظمة تجريبية',
            'status' => 1,
            'user_id' => User::factory()->create()->id,
        ];

        $result = $this->organizationRepository->create($data);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertDatabaseHas('organizations', [
            'name_en' => 'Test Organization',
        ]);
    }

    /** @test */
    public function it_can_update_organization()
    {
        $organization = Organization::factory()->create();
        $updateData = [
            'name_en' => 'Updated Organization',
            'status' => 0,
        ];

        $result = $this->organizationRepository->update($organization, $updateData);

        $this->assertTrue($result);
        $this->assertEquals('Updated Organization', $organization->fresh()->name_en);
        $this->assertEquals(0, $organization->fresh()->status);
    }

    /** @test */
    public function it_can_delete_organization()
    {
        $organization = Organization::factory()->create();

        $result = $this->organizationRepository->delete($organization);

        $this->assertTrue($result);
        $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
    }

    /** @test */
    public function it_can_force_delete_organization()
    {
        $organization = Organization::factory()->create();

        $result = $this->organizationRepository->forceDelete($organization);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('organizations', ['id' => $organization->id]);
    }
}

