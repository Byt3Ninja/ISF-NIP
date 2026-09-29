<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRequest extends FormRequest
{
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
            'currency_code' => 'required|string|max:3|unique:currencies,currency_code',
            'currency_name' => 'required|string|max:100',
            'currency_name_ar' => 'nullable|sometimes|string|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'exchange_rate_to_usd' => 'nullable|numeric|min:0',
            // 'exchange_rate_to_usd' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,6})?$/',
            'enabled' => 'nullable|boolean',
            'flag' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ];
    }
}
