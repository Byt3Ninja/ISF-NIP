<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserIdRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     * @return void
     */ 
    protected function prepareForValidation() 
    {
        $this->merge(['user' => $this->route('user')]);
    }

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
                'user' => ['required',  'exists:users,id'],

        ];
    }
}
