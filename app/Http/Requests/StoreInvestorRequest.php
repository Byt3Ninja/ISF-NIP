<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvestorRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'investmentType' => 'required|string|max:255',
        ];
    }
     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $companyId = auth()->user()->company_id;

            $exists = DB::table('team_members')
                ->where('TeamMember', 'Investor')
                ->where('Company_id', $companyId)
                ->where('Name', $this->input('name'))
                ->where('investorType', $this->input('investmentType'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', __('api.investor_already_exists'));
                $validator->errors()->add('investmentType', __('api.investor_already_exists'));
            }
        });
    }
}
