<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLoginRequest extends FormRequest
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
       $urlRegex = 'regex:/^https?:\/\/[a-zA-Z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]+$/';
       $rules = [
            'id' => ['required', 'integer', 'exists:settings,id'],
            'LINKEDIN_LOGIN' => ['required', 'in:enabled,disabled'],
            'GOOGLE_LOGIN' => ['required', 'in:enabled,disabled'],
        ];

        if ($this->input('LINKEDIN_LOGIN') === 'enabled') {
            $rules['LINKEDIN_CLIENT_ID'] = ['required', 'string'];
            $rules['LINKEDIN_CLIENT_SECRET'] = ['required', 'string'];
            $rules['LINKEDIN_REDIRECT_URI'] = [
              'required',
              'regex:/^https?:\/\/[a-zA-Z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]+$/'
            ];
        } else {
            $rules['LINKEDIN_REDIRECT_URI'] = [
                'nullable',
                'sometimes',
                'regex:/^https?:\/\/[a-zA-Z0-9\-._~:\/?#\[\]@!$&\'()*+,;=%]+$/'
            ];
            $rules['LINKEDIN_CLIENT_ID'] = ['nullable', 'sometimes', 'string'];
            $rules['LINKEDIN_CLIENT_SECRET'] = ['nullable', 'sometimes', 'string'];
        }
        if ($this->input('GOOGLE_LOGIN') === 'enabled') {
            $rules['GOOGLE_CLIENT_ID'] = ['required', 'string'];
            $rules['GOOGLE_CLIENT_SECRET'] = ['required', 'string'];
            $rules['GOOGLE_REDIRECT'] = ['required', $urlRegex];
        } else {
            $rules['GOOGLE_CLIENT_ID'] = ['nullable', 'sometimes', 'string'];
            $rules['GOOGLE_CLIENT_SECRET'] = ['nullable', 'sometimes', 'string'];
            $rules['GOOGLE_REDIRECT'] = ['nullable', 'sometimes', $urlRegex];
        }

        return $rules;
    }

     public function messages()
    {
      return [
        'LINKEDIN_LOGIN.required' => trans('validation.linkedin_login_required'),
        'LINKEDIN_LOGIN.in' => trans('validation.linkedin_login_in'),
        'LINKEDIN_CLIENT_ID.required' => trans('validation.linkedin_client_id_required'),
        'LINKEDIN_CLIENT_SECRET.required' => trans('validation.linkedin_client_secret_required'),
        'LINKEDIN_REDIRECT_URI.required' => trans('validation.linkedin_redirect_required'),
        'LINKEDIN_REDIRECT_URI.regex' => trans('validation.linkedin_redirect_invalid'),

        'GOOGLE_LOGIN.required' => trans('validation.google_login_required'),
        'GOOGLE_LOGIN.in' => trans('validation.google_login_in'),
        'GOOGLE_CLIENT_ID.required' => trans('validation.google_client_id_required'),
        'GOOGLE_CLIENT_SECRET.required' => trans('validation.google_client_secret_required'),
        'GOOGLE_REDIRECT.required' => trans('validation.google_redirect_required'),
        'GOOGLE_REDIRECT.regex' => trans('validation.google_redirect_invalid'),

        'id.required' => trans('validation.settings_id_required'),
        'id.exists' => trans('validation.settings_id_exists'),
    ];
    }

}
