<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class StepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'startDate' => $this->from->format('Y-m-d'),
            'endDate' => $this->to->format('Y-m-d'),
            'program' => [
                'id' => $this->cycle->id ?? null,
                'title' => $this->cycle->title ?? null,
                'description' => $this->cycle->description ?? null,
            ],
            'stepArrange' => $this->arr,
            'stepTypeId' => $this->stype,
            'stepType' => $this->stype_label,
            'formId' =>  $this->form->id ?? null,
            'formName' => $this->form->form_name ?? null,
        ];
    }
}
