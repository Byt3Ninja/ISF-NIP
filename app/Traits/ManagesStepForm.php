<?php

namespace App\Traits;

use App\BuilderField;
use App\Formdata;
use App\Formfield;
use App\Step;
use Illuminate\Support\Facades\DB;

/**
 * Centralized "attach / change the form on a step" behavior, shared by every
 * entry point that lets a manager pick a form for a step (Manage Screening,
 * Manage Final Screening, and the Program Steps "Modify current step" dialog).
 *
 * Keeps steps.data and steps.form_id in sync, re-snapshots the step's fields
 * from the chosen BuilderForm, and refuses to discard existing submissions
 * unless the caller passes an explicit confirmation flag.
 */
trait ManagesStepForm
{
    /**
     * Count existing submissions tied to a step.
     * form_data = founder/applicant answers (all step types).
     * Note: final_data (judge evaluation answers) intentionally out of scope.
     */
    protected function stepSubmissionCounts(Step $step): array
    {
        $formData  = Formdata::where('step_id', $step->id)->distinct('company_id')->count('company_id');

        return [
            'form_data' => $formData,
            'total'     => $formData,
        ];
    }

    /**
     * Apply (or change/clear) the form attached to a step.
     *
     * @param  int|null  $newFormId  BuilderForm id, or null to detach the form.
     * @param  bool      $confirmed  Caller has confirmed destroying submissions.
     * @return array{status:string, counts?:array}
     */
    protected function applyFormToStep(Step $step, $newFormId, bool $confirmed): array
    {
        $newFormId = ($newFormId === null || $newFormId === '') ? null : (int) $newFormId;
        $currentFormId = $step->data !== null && $step->data !== '' ? (int) $step->data : null;

        $snapshotExists = Formfield::where('step_id', $step->id)->exists();

        // Nothing to do: same form already attached AND a snapshot is present.
        if ($newFormId === $currentFormId && $snapshotExists) {
            return ['status' => 'noop'];
        }

        $counts = $this->stepSubmissionCounts($step);
        if ($counts['total'] > 0 && ! $confirmed) {
            return ['status' => 'needs_confirmation', 'counts' => $counts];
        }

        DB::transaction(function () use ($step, $newFormId, $counts) {
            if ($counts['total'] > 0) {
                Formdata::where('step_id', $step->id)->delete();
            }

            // Drop the old field snapshot and copy the new form's fields.
            Formfield::where('step_id', $step->id)->delete();

            if ($newFormId !== null) {
                $fields = BuilderField::where('builder_form_id', $newFormId)->orderBy('id')->get();
                foreach ($fields as $field) {
                    Formfield::insert([
                        'step_id'          => $step->id,
                        'field_id'         => $field->field_id,
                        'options'          => json_encode($field->options),
                        'builder_forms_id' => $field->builder_form_id,
                    ]);
                }
            }

            $step->update([
                'data'    => $newFormId,
                'form_id' => $newFormId,
            ]);
        });

        return ['status' => 'done'];
    }
}
