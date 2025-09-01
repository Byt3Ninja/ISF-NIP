<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreShareholderRequest extends FormRequest
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
            'role' => 'nullable|string|max:150',
            'ownership' => 'required|numeric|min:0|max:100',
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
            $newOwnership = floatval($this->input('ownership', 0));

            $existing = DB::table('team_members')
                ->where('Company_id', $companyId)
                ->where('TeamMember', 'Shareholder')
                ->sum('PresentagShereholder');

            if (($existing + $newOwnership) > 100) {
                throw new HttpResponseException(
                $this->error(__('api.total_shareholder_ownership_exceeded'), 400)
             );
            }
        });
    }
}
