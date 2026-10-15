<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateCurrencyApiRequest extends FormRequest
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
            'currencyCode' => 'nullable|string|max:3|unique:currencies,currency_code,' . $this->route('id'),
            'currencyName' => 'nullable|string|max:100',
            'currencySymbol' => 'nullable|string|max:10',
            'exchangeRateToUsd' => 'nullable|numeric|min:0',
            'enabled' => 'nullable|boolean',
            'flag' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
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
