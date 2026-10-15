<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateProgramRequest extends FormRequest
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
            'organizationId' => 'nullable|exists:organizations,id',
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:200',
            'start' => 'nullable|date|before:end',
            'end' => 'nullable|date|after:start',
            'programGuidelineFile' => 'nullable|file|mimes:pdf|max:10240', 
            'financialPolicyPdf' => 'nullable|file|mimes:pdf|max:10240',
            'legalPolicyPdf' => 'nullable|file|mimes:pdf|max:10240',
            'delegationPdf' => 'nullable|file|mimes:pdf|max:10240',
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
