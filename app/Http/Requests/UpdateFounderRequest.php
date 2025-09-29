<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateFounderRequest extends FormRequest
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
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('team_members', 'email')->ignore($this->route('id')),
            ],
            'linkedin' => 'sometimes|required|url',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
