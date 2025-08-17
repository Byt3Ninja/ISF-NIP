<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateCompanyProfileRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'address' => 'nullable|string|max:500',
            'sector_id' => 'nullable|exists:sectors,id',
            'stage_id' => 'nullable|exists:stages,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'address' => 'nullable|string|max:100',
            'yearsOfOperations' => 'nullable|string',
            'productOrService' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'googleMapUrl' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'facebook' => 'nullable|url',
            'x' => 'nullable|url',
            'youtube' => 'nullable|url',
            'instagram' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'aboutStartup' => 'nullable|string',
            'aboutProblem' => 'nullable|string',
            'aboutMarketOpportunities' => 'nullable|string',
            'aboutCompetitors' => 'nullable|string',
            'aboutMonetization' => 'nullable|string',
            'aboutChallenges' => 'nullable|string',
            'sustainabilityFeatures' => 'nullable|array',
            'currency' => 'nullable|string',
            'preMoneyEvaluation' => 'nullable|numeric',
            'raisedFund' => 'nullable|numeric',
            'fundingTarget' => 'nullable|numeric',
            'targetMarket' => 'nullable|array',
            'targetMarket.*' => 'exists:countries,id',
            'businessType' => 'nullable|string',
            'financialPlan' => 'nullable|string',
            'financialPlanAttachment' => 'nullable|file',
            'teamNumber' => 'nullable|integer',
            'workingTeamDetails' => 'nullable|string',
            'founders' => 'nullable|array',
            'founders.*.name' => 'required|string',
            'founders.*.email' => 'nullable|email',
            'founders.*.linkedin' => 'nullable|url',
            'shareholders' => 'nullable|array',
            'shareholders.*.name' => 'string',
            'shareholders.*.role' => 'nullable|string',
            'shareholders.*.ownership' => 'nullable|numeric',
            'investors' => 'nullable|array',
            'investors.*.name' => 'string',
            'investors.*.investmentType' => 'nullable|string',
            'pitchDeckFile' => 'nullable|file',
            'pitchDeckVideoUrl' => 'nullable|url',
            'documents' => 'nullable|array',
            'documents.*' => 'file',
            'achievedMilestones' => 'nullable|array',
            'plannedMilestones' => 'nullable|array',
            'archivedMilestones' => 'nullable|array',
            'businessModel' => 'nullable|string',
            'businessModelDescription' => 'nullable|string',
            'businessPlanAttachment' => 'nullable|file',
            'haveStartup' => 'nullable|in:Yes,No',
            'CreatedTax' => 'required_if:haveStartup,Yes|boolean',
            'CommercialRegister' => 'required_if:haveStartup,Yes|string|max:255',
            'TaxCard' => 'required_if:haveStartup,Yes|string|max:255',
            'Country_Register_id' => 'required_if:haveStartup,Yes|exists:countries,id',
        ];
    }

      protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
