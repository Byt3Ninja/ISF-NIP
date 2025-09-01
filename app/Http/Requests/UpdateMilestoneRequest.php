<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMilestoneRequest extends FormRequest
{
    use ApiResponder;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan' => 'nullable|in:Achieved_Milestones,Planned_Milestones,Archived_Milestones',
            'name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('milestones', 'name')->ignore($this->route('id')),
            ],
            'description' => 'nullable|string|max:200',
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
