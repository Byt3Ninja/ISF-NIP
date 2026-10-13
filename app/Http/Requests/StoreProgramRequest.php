<?php

namespace App\Http\Requests;

use App\Cycle;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class StoreProgramRequest extends FormRequest
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
            'organizationId' => 'required|exists:organizations,id',
            'title' => 'required|string|max:250',
            'description' => 'required|string|max:200',
            'start' => 'required|date|before:end',
            'end' => 'required|date|after:start',
            'type' => ['required', Rule::in(array_keys(Cycle::programTypes()))],
            'programGuidelineFile' => 'nullable|file|mimes:pdf|max:2048',
            'financialPolicyPdf' => 'nullable|file|mimes:pdf|max:2048',
            'legalPolicyPdf' => 'nullable|file|mimes:pdf|max:2048',
            'delegationPdf' => 'nullable|file|mimes:pdf|max:2048',
            'imgUrl' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'budgetAllocation' => 'nullable|numeric|min:0',
            'currencyType' => 'nullable|string|max:10',
            'programGuideline' => 'nullable|string',
            'financialGuideline' => 'nullable|string|max:200',
            'legalGuideline' => 'nullable|string|max:200',
            'allowNonCompany' => 'nullable|boolean',
            'autoApproveMentorshipRequests' => 'nullable|boolean',
            'private' => 'nullable|boolean',
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(
                __('api.validation_error'),
                422,
                $validator->errors()
            )
        );
    }
}
