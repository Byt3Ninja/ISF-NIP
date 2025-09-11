<?php
namespace App\Services;

use App\Repositories\FormRepository;

class FormService
{
    protected FormRepository $formRepo;

    public function __construct(FormRepository $formRepo)
    {
        $this->formRepo = $formRepo;
    }

   public function getAvailableForms(array $filters = [])
{
    $forms = $this->formRepo->getAllFormsWithSteps();

    return $forms->map(function ($form) use ($filters) {
        $stepTypes = $form->steps
            ->pluck('stype_label')
            ->unique()
            ->filter()
            ->values();

        if (!empty($filters['stepType']) && !$stepTypes->contains($filters['stepType'])) {
            return null;
        }

        return [
            'id' => $form->id,
            'name' => $form->form_name,
            'stepTypes' => $stepTypes,
            'createdAt' => $form->created_at->toIso8601String(),
            'lastModified' => $form->updated_at->toIso8601String()
        ];
    })->filter()->values();
}

}

