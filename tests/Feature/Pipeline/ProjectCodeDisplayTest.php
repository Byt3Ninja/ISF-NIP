<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Resources\CompanyResource;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * P1-06: where people and other systems read the project number.
 */
class ProjectCodeDisplayTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    public function test_the_idea_profile_page_shows_the_project_number(): void
    {
        $this->fakeOutboundServices();
        $program = $this->makeFinalScreeningProgram();
        $admin = $this->makeUser('admin', null);
        Company::backfillProjectCodes(); // the fixture inserts its company the way older rows exist
        $code = Company::findOrFail($program['company'])->project_code;

        $this->actingAs($admin)
            ->get(route('company.profile_view', $program['company']))
            ->assertOk()
            ->assertSee($code);
    }

    public function test_the_api_resources_carry_the_project_number(): void
    {
        $company = Company::factory()->create();

        $this->assertSame($company->project_code, (new CompanyResource($company))->toArray(request())['project_code']);
        $this->assertSame($company->project_code, (new CompanyProfileResource($company))->toArray(request())['project_code']);
    }
}
