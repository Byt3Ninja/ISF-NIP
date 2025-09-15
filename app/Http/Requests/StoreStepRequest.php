<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStepRequest extends FormRequest
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
        $rules = [
        'title' => 'required|string|max:100|unique:steps,title',
        'description' => 'required|string|max:150',
        'stepType' => 'required|string|max:50',
        'stepTypeId' => 'required|integer',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after:startDate',
        'stepArrange' => 'nullable|integer',
        'count_team_number' => 'nullable|integer|min:0|gt:0',
    ];

    if (in_array((int) $this->input('stepTypeId'), [1, 5])) {
        $rules['formId'] = 'required|numeric|exists:builder_forms,id';
        $rules['formName'] = 'required|string|max:255';
    }

    if ((int) $this->input('stepTypeId') === 2) {
        $rules['screeningType'] = 'required|string|in:onetoall,grouptoall,grouptogroup';
    }

     return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->validationError(__('api.validation_error'), 422, $validator->errors())
            );
        }

        parent::failedValidation($validator);
    }
}
