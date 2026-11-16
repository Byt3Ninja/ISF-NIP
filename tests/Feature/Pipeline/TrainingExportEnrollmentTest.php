<?php

namespace Tests\Feature\Pipeline;

use App\CompanyEnrollment;
use App\Exports\TrainingResultExport;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-02, training results export: the step filter and the program shown come from
 * the company's enrollments, so a company working in another sub-program is still
 * exported for the step it is on here.
 */
class TrainingExportEnrollmentTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    public function test_a_company_is_exported_for_the_step_it_is_enrolled_on_even_when_its_pointer_is_elsewhere(): void
    {
        $this->fakeOutboundServices();
        Excel::fake();

        $organization = $this->organization();
        $pointerCycle = $this->program($organization);
        $trainingCycle = $this->program($organization, ['title' => 'Training Program']);
        $trainingStep = $this->step($trainingCycle, ['stype' => 4, 'title' => 'Training Step']);

        [$companyId] = $this->startup($pointerCycle, ['name_en' => 'Exported Idea']);
        $enrollment = new CompanyEnrollment(['step_id' => $trainingStep, 'status' => CompanyEnrollment::STATUS_ACTIVE, 'joined_at' => now()]);
        $enrollment->company_id = $companyId;
        $enrollment->cycle_id = $trainingCycle;
        $enrollment->save();
        // On the same pointer cycle but not on the training step: must not be exported.
        $this->startup($pointerCycle, ['name_en' => 'Other Idea']);

        $manager = $this->actor('organizationmanager', ['organization_id' => $organization]);
        $this->grant('organizationmanager', ['trainings.results_export']);

        $this->actingAs($manager)->get(route('trainings.results_export', ['location' => 0, 'step' => $trainingStep]));

        Excel::assertDownloaded('training_results_report.xlsx', function (TrainingResultExport $export) use ($companyId) {
            $rows = $export->collection();

            return $rows->count() === 1 && (int) $rows->first()['ID'] === $companyId;
        });
    }
}
