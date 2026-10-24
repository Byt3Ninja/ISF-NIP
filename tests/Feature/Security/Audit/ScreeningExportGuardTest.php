<?php

namespace Tests\Feature\Security\Audit;

use App\Jobs\ExportResScreenData;
use Illuminate\Support\Facades\Bus;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * The unnamed /export-Result route skips the permission middleware, and its guard used
 * ownsOrganization(), which a founder passes for their own program: a founder could have
 * every startup's screening results and judge scores emailed to them.
 */
class ScreeningExportGuardTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        Bus::fake([ExportResScreenData::class]);
    }

    public function test_founder_cannot_export_their_programs_screening_results(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org);
        $step = $this->step($cycle, ['stype' => 2]);
        [, $founder] = $this->startup($cycle);

        $this->actingAs($founder)->get('/dashboard/export-Result?step=' . $step)->assertForbidden();

        Bus::assertNotDispatched(ExportResScreenData::class);
    }

    public function test_program_manager_can_still_export(): void
    {
        $org = $this->organization();
        $step = $this->step($this->program($org), ['stype' => 2]);

        $this->actingAs($this->manager($org))->get('/dashboard/export-Result?step=' . $step)->assertOk();

        Bus::assertDispatched(ExportResScreenData::class);
    }
}
