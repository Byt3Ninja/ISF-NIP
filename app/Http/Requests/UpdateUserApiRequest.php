<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateUserApiRequest extends FormRequest
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
        $userId = $this->route('id');
        return [
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'exists:roles,id'],
            'status' => ['nullable', 'string'],
        ];
    }
       protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError('Validation Error', 422, $validator->errors())
        );
    }
}
