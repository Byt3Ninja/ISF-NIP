<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInvestorRequest extends FormRequest
{
    use ApiResponder;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'investmentType' => 'sometimes|required|string|max:255',
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

            $name = $this->input('name');
            $investmentType = $this->input('investmentType');

            if ($name && $investmentType) {
                $exists = DB::table('team_members')
                    ->where('Company_id', $companyId)
                    ->where('TeamMember', 'Investor')
                    ->where('Name', $name)
                    ->where('investorType', $investmentType)
                    ->where('id', '!=', $this->route('id'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', __('api.investor_already_exists'));
                    $validator->errors()->add('investmentType', __('api.investor_already_exists'));
                }
            }
        });
    }
}

