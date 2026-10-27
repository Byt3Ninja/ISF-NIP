<?php

namespace Tests\Feature\Correctness;

use App\Exports\CompaniesExport;
use App\Services\AdvancedExportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Correctness audit, exports cluster: C5 (startup export capped at 9 rows), C17 (organization
 * filter ignored because of the `orgnization` key), C32 (advanced export ignores its organization).
 */
class ExportsCorrectnessTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        Carbon::setTestNow('2026-10-24 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Export the startups list filtered the way the companies screen does (it posts `orgnization`). */
    private function exportedRows(int $organizationId): int
    {
        Excel::fake();
        $this->grant('admin', ['users.companies_export']);

        $this->actingAs($this->actor('admin'))
            ->get("/dashboard/companies/export?format=csv&orgnization={$organizationId}");

        $rows = null;
        Excel::assertDownloaded('startups_export_24-10-2026_12-00.csv', function (CompaniesExport $export) use (&$rows) {
            $rows = $export->collection()->count();

            return true;
        });

        return $rows;
    }

    public function test_c17_export_applies_the_organization_filter(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org);
        $this->startup($cycle);
        $this->startup($cycle);

        $this->assertSame(2, $this->exportedRows($org));
    }

    public function test_c5_export_is_not_capped_at_one_page(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org);
        for ($i = 0; $i < 12; $i++) {
            $this->startup($cycle);
        }

        $this->assertSame(12, $this->exportedRows($org));
    }

    public function test_c32_advanced_export_contains_only_the_requested_organization(): void
    {
        Storage::fake('local');
        $orgA = $this->organization();
        $orgB = $this->organization();
        $subAdmin = $this->actor('subadmin');
        foreach ([$orgA, $orgB] as $org) {
            DB::table('user_organizations')->insert(['user_id' => $subAdmin->id, 'organization_id' => $org, 'created_at' => now(), 'updated_at' => now()]);
            $this->submission($org);
        }

        $this->actingAs($subAdmin);
        app(AdvancedExportService::class)->generateExport($orgA);

        $meta = collect(Storage::disk('local')->allFiles())->first(fn ($path) => str_ends_with($path, 'metadata.json'));
        $this->assertNotNull($meta, 'export metadata was not written');
        $this->assertSame(1, json_decode(Storage::disk('local')->get($meta), true)['count']);
    }

    /** One startup's answer to one form question in a program of $organizationId. */
    private function submission(int $organizationId): void
    {
        $cycle = $this->program($organizationId);
        $step = $this->step($cycle);
        [$companyId, $founder] = $this->startup($cycle, ['step' => $step]);
        $formFieldId = DB::table('form_fields')->insertGetId([
            'step_id' => $step, 'field_id' => DB::table('fields')->value('id'),
            'options' => json_encode(['label' => 'Audit question', 'type' => 'text']),
        ]);
        DB::table('form_data')->insert([
            'field_id' => $formFieldId, 'company_id' => $companyId, 'user_id' => $founder->id,
            'form_id' => $step, 'step_id' => $step, 'question' => 'Audit question', 'answer' => 'Audit answer',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
