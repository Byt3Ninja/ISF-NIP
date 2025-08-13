<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateProfileRequest extends FormRequest
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
       $userId = $this->user()->id;
        return [
            'firstName'     => 'nullable|string|max:255',
            'lastName'      => 'nullable|string|max:255',
            'countryCode'      => 'nullable|string|max:10',
            'country_code_w_a'  => 'nullable|string|max:10',
            'phone'             => 'nullable|string|max:20',
            'whatsapp'          => 'nullable|string|max:20',
            'email'             => ['nullable', 'email', Rule::unique('users')->ignore($userId)],
            'zipCode'          => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'title'          => 'nullable|string|max:255',
            'language'          => 'nullable|string|max:10',
            'timezone'          => 'nullable|string|max:100',
            'currency'          => 'nullable|string|max:10',
            'country_id'        => 'nullable|exists:countries,id',
            'state_id'          => 'nullable|exists:states,id',
            'imgUrl'            => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
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
