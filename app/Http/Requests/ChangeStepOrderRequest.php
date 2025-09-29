<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;

class ChangeStepOrderRequest extends FormRequest
{
    use ApiResponder;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newPosition' => 'required|integer|min:1',
            'direction' => 'required|in:before,after',
            'targetStepId' => 'required|exists:steps,id',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
