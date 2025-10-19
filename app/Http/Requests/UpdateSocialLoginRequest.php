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
       $rules = [
            'id' => ['required', 'integer', 'exists:settings,id'],
            'LINKEDIN_LOGIN' => ['required', 'in:enabled,disabled'],
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

        return $rules;
    }

     public function messages()
    {
        $isArabic = app()->getLocale() === 'ar';

        if ($isArabic) {
            return [
                'LINKEDIN_LOGIN.required' => 'يرجى اختيار حالة تسجيل الدخول عبر لينكدإن.',
                'LINKEDIN_LOGIN.in' => 'حالة تسجيل الدخول عبر لينكدإن غير صحيحة.',
                'LINKEDIN_CLIENT_ID.required' => 'يرجى إدخال معرف العميل الخاص بلينكدإن.',
                'LINKEDIN_CLIENT_SECRET.required' => 'يرجى إدخال السرّ الخاص بلينكدإن.',
                'LINKEDIN_REDIRECT_URI.required' => 'يرجى إدخال رابط إعادة التوجيه الخاص بلينكدإن.',
                'LINKEDIN_REDIRECT_URI.url' => 'يرجى إدخال رابط إعادة توجيه صالح',
                'id.required' => 'معرف الإعداد مطلوب.',
                'id.exists' => 'سجل الإعداد غير موجود.',
            ];
        }

        return [
            'LINKEDIN_LOGIN.required' => 'Please choose LinkedIn login state.',
            'LINKEDIN_LOGIN.in' => 'Invalid LinkedIn login state.',
            'LINKEDIN_CLIENT_ID.required' => 'Please complete all LinkedIn configuration fields before saving.',
            'LINKEDIN_CLIENT_SECRET.required' => 'Please complete all LinkedIn configuration fields before saving.',
            'LINKEDIN_REDIRECT_URI.required' => 'Please complete all LinkedIn configuration fields before saving.',
            'LINKEDIN_REDIRECT_URI.url' => 'Please enter a valid Redirect URL.',
            'id.required' => 'Settings id is required.',
            'id.exists' => 'Settings record not found.',
        ];
    }

}
