<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
         $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName'  => ['required', 'string', 'max:255'],
            'gender'        => ['required'],
            'phone'         => ['required', 'unique:users,phone'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'role'          => ['required', 'in:Mentor,Registered,Judger'],
            'title'      => ['nullable', 'string'],
            'userbio'       => ['nullable', 'string'],
            'linkedin'      => ['nullable', 'string'],
            'expertise'     => ['nullable', 'array', 'exists:expertises,id'],
        ];

        if ($this->input('role') === 'Mentor') {
            $rules['title'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

     public function messages(): array
    {
        return [
            'title.required' => __('api.title_required'),
        ];
    }
}
