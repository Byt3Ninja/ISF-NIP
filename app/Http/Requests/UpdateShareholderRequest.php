<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateShareholderRequest extends FormRequest
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
            'role' => 'sometimes|nullable|string|max:150',
            'ownership' => 'sometimes|required|numeric|min:0|max:100',
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

            $currentShareholderId = $this->route('id');

            $existing = DB::table('team_members')
                ->where('Company_id', $companyId)
                ->where('TeamMember', 'Shareholder')
                ->where('id', '!=', $currentShareholderId)
                ->sum('PresentagShereholder');

            if (($existing + $newOwnership) > 100) {
                throw new HttpResponseException(
                    $this->error(__('api.total_shareholder_ownership_exceeded'), 400)
                );
            }
        });
    }
}

