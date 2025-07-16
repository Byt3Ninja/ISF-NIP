<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class StoreUserApiRequest extends FormRequest
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
                'firstName' => ['required', 'string', 'max:255'],
                'lastName' => ['required', 'string', 'max:255'],
                'email' => 'email|required|unique:users,email',
                'phone' => 'required|unique:users,phone',
                'gender' => 'required|string|in:male,female',
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'role' => 'required|exists:roles,id',
            ];
    }

       protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError('Validation Error', 422, $validator->errors())
        );
    }
}
