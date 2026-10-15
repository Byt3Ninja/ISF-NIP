<?php
namespace App\Services;

use App\Cycle;
use App\Formfield;
use App\BuilderField;
use App\Traits\ApiResponder;
use App\Repositories\StepRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
class StepService
{
    use ApiResponder;
    protected StepRepository $stepRepo;

    public function __construct(StepRepository $stepRepo)
    {
        $this->stepRepo = $stepRepo;
    }

    public function createStep(array $data)
    {
        $stype = $data['stepTypeId'];
        $formId = null;

        // Hackathon
        if ($stype == 6) {
            $cycle = Cycle::find($data['programId']);
            if (!$cycle || $cycle->type !== 'Hackathon') {
                throw new HttpResponseException(
                    $this->validationError(__('api.validation_error'), 422, [
                        'cycle' => [__('admin.HackathonAllowed')]
                    ])
                );
            }
        }

        // Determine data field by type
        if ($stype == 1) {
            // Custom Form
            $formId = $data['formId'] ?? null;
        } elseif ($stype == 2) {
            // Screening
            $formId = $data['screeningType'] ?? null;
        } elseif ($stype == 4) {
            // Training
            $data['list'] = $data['RecordedSelect'] ?? null;
        } elseif ($stype == 5) {
            // Final Evaluation
            $formId = $data['formId'] ?? null;

            if (!$formId) {
                throw ValidationException::withMessages(['formId' => 'You must add a form first.']);
            }
        }

        $step = $this->stepRepo->create([
            'cycle_id' => $data['programId'],
            'title' => $data['title'],
            'description' => $data['description'],
            'stype' => $stype,
            'data' => $formId,
            'arr' => $data['stepArrange'],
            'from' => $data['startDate'],
            'to' => $data['endDate'],
            'list' => $data['list'] ?? null,
            'count_team_number' => $data['count_team_number'] ?? 0,
        ]);

        if ($formId && in_array($stype, [1, 5])) {
            $fields = BuilderField::where('builder_form_id', $formId)->orderBy('id')->get();
            foreach ($fields as $field) {
                // step_id, field_id and builder_forms_id are foreign keys excluded
                // from mass-assignment ($fillable); set them explicitly from the
                // trusted server-side context.
                $formField = new Formfield([
                    'options' => json_encode($field->options),
                ]);
                $formField->step_id = $step->id;
                $formField->field_id = $field->field_id;
                $formField->builder_forms_id = $field->builder_form_id;
                $formField->save();
            }
        }

        return $step;
    }

    public function updateStep(array $data, $stepId)
{
    // When the caller names a program, the step must belong to it; otherwise an
    // owned programId could be used to edit (and re-parent) another program's step.
    $step = isset($data['programId'])
        ? $this->stepRepo->findInProgram($data['programId'], $stepId)
        : $this->stepRepo->find($stepId);

     if (!$step) {
        throw new HttpResponseException(
            $this->notFound(__('api.step_not_found'))
        );
    }

    $stype = $data['stepTypeId'] ?? $step->stype;
    $formId = null;

    // Hackathon validation
    if ($stype == 6) {
        $cycle = Cycle::find($data['programId']);
        if (!$cycle || $cycle->type !== 'Hackathon') {
            throw new HttpResponseException(
                $this->validationError(__('api.validation_error'), 422, [
                    'cycle' => [__('admin.HackathonAllowed')]
                ])
            );
        }
    }

    // Determine data by type
    if ($stype == 1) {
        $formId = $data['formId'] ?? null;
    } elseif ($stype == 2) {
        $formId = $data['screeningType'] ?? null;
    } elseif ($stype == 4) {
        $data['list'] = $data['RecordedSelect'] ?? null;
    } elseif ($stype == 5) {
        $formId = $data['formId'] ?? null;

        if (!$formId) {
            throw new HttpResponseException(
                $this->validationError(__('api.validation_error'), 422, [
                    'formId' => ['You must add a form first.']
                ])
            );
        }
    }

    $updateData = [
        'cycle_id' => $data['programId'] ?? $step->cycle_id,
        'title' => $data['title'] ?? $step->title,
        'description' => $data['description'] ?? $step->description,
        'stype' => $stype,
        'data' => $formId,
        'arr' => $data['stepArrange'] ?? $step->arr,
        'from' => $data['startDate'] ?? $step->from,
        'to' => $data['endDate'] ?? $step->to,
        'list' => $data['list'] ?? $step->list,
        'count_team_number' => $data['count_team_number'] ?? $step->count_team_number,
    ];

    $this->stepRepo->update($step, $updateData);

    return $step;
}

public function deleteStep($stepId, $programId = null)
{
    // Same rule as updateStep: scope the lookup to the program when one is given.
    $step = $programId !== null
        ? $this->stepRepo->findInProgram($programId, $stepId)
        : $this->stepRepo->find($stepId);

    if (!$step) {
        throw new HttpResponseException(
            $this->notFound(__('api.step_not_found'))
        );
    }

    $this->stepRepo->delete($step);

    return true;
}

public function reorderSteps($programId, array $steps)
{
    $updated = [];

    foreach ($steps as $stepData) {
        $step = $this->stepRepo->findInProgram($programId, $stepData['id']);

        if ($step) {
            $step->arr = $stepData['stepArrange'];
            $step->save();

            $updated[] = [
                'id' => $step->id,
                'stepArrange' => $step->arr,
            ];
        }
    }

    return $updated;
}

public function changeStepOrder($programId, $stepId, array $data)
{
    $step = $this->stepRepo->findInProgram($programId, $stepId);
    $targetStep = $this->stepRepo->findInProgram($programId, $data['targetStepId']);

    if (!$step || !$targetStep) {
        throw new HttpResponseException(
            $this->notFound(__('api.step_not_found'))
        );
    }

    $allSteps = $this->stepRepo->getByProgram($programId)->sortBy('arr')->values();

    $stepsWithoutMoving = $allSteps->reject(fn ($s) => $s->id === $step->id)->values();

    // Determine where to insert
    $targetIndex = $stepsWithoutMoving->search(fn ($s) => $s->id === $targetStep->id);
    $insertIndex = $data['direction'] === 'before' ? $targetIndex : $targetIndex + 1;

    // Insert moving step at new position
    $stepsWithoutMoving->splice($insertIndex, 0, [$step]);

    // Reassign stepArrange
    $affected = [];
    foreach ($stepsWithoutMoving as $index => $s) {
        $s->arr = $index + 1;
        $s->save();
        $affected[] = ['id' => $s->id, 'stepArrange' => $s->arr];
    }

    return [
        'id' => $step->id,
        'title' => $step->title,
        'stepArrange' => $step->arr,
        'affectedSteps' => $affected
    ];
}

public function getStepTypes()
{
        return collect([
            [
                'id' => 1,
                'name' => __('cycles.Custom Form'),
                'requiresForm' => true,
                'allowsScreeningType' => false,
            ],
            [
                'id' => 2,
                'name' => __('cycles.Screening'),
                'requiresForm' => false,
                'allowsScreeningType' => true,
            ],
            [
                'id' => 3,
                'name' => __('cycles.Mentorship'),
                'requiresForm' => false,
                'allowsScreeningType' => false,
            ],
            [
                'id' => 4,
                'name' => __('cycles.Training'),
                'requiresForm' => false,
                'allowsScreeningType' => false,
            ],
            [
                'id' => 5,
                'name' => __('cycles.Final evaluation'),
                'requiresForm' => true,
                'allowsScreeningType' => false,
            ],
            [
                'id' => 6,
                'name' => __('admin.Team Formation'),
                'requiresForm' => false,
                'allowsScreeningType' => false,
            ],
        ]);
}

}
