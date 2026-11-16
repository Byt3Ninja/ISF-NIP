<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use Tests\TestCase;

/**
 * P1-06: every idea gets one project number when it is created and keeps it for
 * good, so the same project can be recognised across sub-programs and funders.
 */
class ProjectCodeTest extends TestCase
{
    public function test_a_new_company_gets_a_project_code(): void
    {
        config(['app.project_code_prefix' => 'ISF']);

        $company = Company::factory()->create();

        $expected = 'ISF-' . $company->created_at->format('y') . '-' . str_pad((string) $company->id, 6, '0', STR_PAD_LEFT);
        $this->assertSame($expected, $company->project_code);
        $this->assertSame($expected, $company->fresh()->project_code);
    }

    public function test_the_prefix_comes_from_configuration(): void
    {
        config(['app.project_code_prefix' => 'PRJ']);

        $this->assertStringStartsWith('PRJ-', Company::factory()->create()->project_code);
    }

    public function test_the_project_code_cannot_be_changed_by_an_edit(): void
    {
        $company = Company::factory()->create();
        $code = $company->project_code;

        $company->update(['name_en' => 'Renamed', 'project_code' => 'SOMETHING-ELSE']);

        $this->assertSame($code, $company->fresh()->project_code);
        $this->assertSame('Renamed', $company->fresh()->name_en);
    }

    public function test_the_project_code_cannot_be_changed_by_setting_it_directly(): void
    {
        $company = Company::factory()->create();
        $code = $company->project_code;

        $company->project_code = 'SOMETHING-ELSE';
        $company->save();

        $this->assertSame($code, $company->fresh()->project_code);
    }

    public function test_two_companies_never_share_a_project_code(): void
    {
        $first = Company::factory()->create();
        $second = Company::factory()->create();

        $this->assertNotSame($first->project_code, $second->project_code);
    }

    public function test_backfill_numbers_companies_that_have_none_and_leaves_the_rest(): void
    {
        config(['app.project_code_prefix' => 'ISF']);
        $older = Company::withoutEvents(fn () => Company::factory()->create());
        $numbered = Company::factory()->create();
        $this->assertNull($older->fresh()->project_code);

        Company::backfillProjectCodes();

        $this->assertSame(
            Company::formatProjectCode($older->id, $older->created_at),
            $older->fresh()->project_code
        );
        $this->assertSame($numbered->project_code, $numbered->fresh()->project_code);
    }
}
