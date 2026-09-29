<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStep2Request extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Contact Information
            'contact_email' => 'nullable|email|max:255|regex:/^[a-zA-Z0-9._%+\-@]+$/',
            'country_code' => 'required_with:contact_phone|nullable|string|max:10',
            'contact_phone' => 'nullable|string|max:20|regex:/^[0-9\s\-\+\(\)]+$/',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'google_map_url' => ['nullable', 'string', 'max:500', 'regex:#^https?://(www\.)?(google\.[a-z]{2,3}(/maps)?|maps\.google\.[a-z]{2,3}|goo\.gl|maps\.app\.goo\.gl)#i'],

            // Social Media (all optional)
            'linkedin_company_url' => 'nullable|url|max:255',
            'twitter_handle' => 'nullable|url|max:255',
            'facebook_page_url' => 'nullable|url|max:255',
            'instagram_profile_url' => 'nullable|url|max:255',
            'youtube_channel_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return [
                // Contact Information
                'contact_email.required' => 'يرجى إدخال البريد الإلكتروني',
                'contact_email.email' => 'يرجى إدخال بريد إلكتروني صالح',
                'contact_email.max' => 'البريد الإلكتروني يجب أن لا يتجاوز 255 حرفاً',
                'country_code.required_with' => 'يرجى اختيار رمز الدولة',
                'contact_phone.max' => 'رقم الهاتف يجب أن لا يتجاوز 20 رقماً',
                'contact_phone.regex' => 'يرجى إدخال رقم هاتف صالح',
                'country_id.required' => 'يرجى اختيار الدولة',
                'country_id.exists' => 'الدولة المحددة غير صالحة',
                'state_id.required' => 'يرجى اختيار المنطقة',
                'state_id.exists' => 'المنطقة المحددة غير صالحة',
                'city_id.exists' => 'المدينة المحددة غير صالحة',
                'address.required' => 'يرجى إدخال العنوان',
                'address.max' => 'العنوان يجب أن لا يتجاوز 500 حرف',
                'google_map_url.required' => 'يرجى إدخال رابط Google Maps',
                'google_map_url.max' => 'رابط Google Maps يجب أن لا يتجاوز 500 حرف',
                'google_map_url.regex' => 'يرجى إدخال رابط Google Maps صالح',

                // Social Media
                'linkedin_company_url.url' => 'يرجى إدخال رابط صالح',
                'linkedin_company_url.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
                'twitter_handle.url' => 'يرجى إدخال رابط صالح',
                'twitter_handle.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
                'facebook_page_url.url' => 'يرجى إدخال رابط صالح',
                'facebook_page_url.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
                'instagram_profile_url.url' => 'يرجى إدخال رابط صالح',
                'instagram_profile_url.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
                'youtube_channel_url.url' => 'يرجى إدخال رابط صالح',
                'youtube_channel_url.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
                'tiktok_url.url' => 'يرجى إدخال رابط صالح',
                'tiktok_url.max' => 'الرابط يجب أن لا يتجاوز 255 حرفاً',
            ];
        }

        return [
            // Contact Information
            'contact_email.required' => 'Please enter the email address.',
            'contact_email.email' => 'Please enter a valid email address.',
            'contact_email.max' => 'Email must not exceed 255 characters.',
            'country_code.required_with' => 'Please select the country code.',
            'contact_phone.max' => 'Phone number must not exceed 20 digits.',
            'contact_phone.regex' => 'Please enter a valid phone number.',
            'country_id.required' => 'Please select the country.',
            'country_id.exists' => 'The selected country is invalid.',
            'state_id.required' => 'Please select the state/region.',
            'state_id.exists' => 'The selected state/region is invalid.',
            'city_id.exists' => 'The selected city is invalid.',
            'address.required' => 'Please enter the address.',
            'address.max' => 'Address must not exceed 500 characters.',
            'google_map_url.required' => 'Please enter the Google Maps URL.',
            'google_map_url.max' => 'Google Maps URL must not exceed 500 characters.',
            'google_map_url.regex' => 'Please enter a valid Google Maps URL.',

            // Social Media
            'linkedin_company_url.url' => 'Please enter a valid URL.',
            'linkedin_company_url.max' => 'URL must not exceed 255 characters.',
            'twitter_handle.url' => 'Please enter a valid URL.',
            'twitter_handle.max' => 'URL must not exceed 255 characters.',
            'facebook_page_url.url' => 'Please enter a valid URL.',
            'facebook_page_url.max' => 'URL must not exceed 255 characters.',
            'instagram_profile_url.url' => 'Please enter a valid URL.',
            'instagram_profile_url.max' => 'URL must not exceed 255 characters.',
            'youtube_channel_url.url' => 'Please enter a valid URL.',
            'youtube_channel_url.max' => 'URL must not exceed 255 characters.',
            'tiktok_url.url' => 'Please enter a valid URL.',
            'tiktok_url.max' => 'URL must not exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'contact_email' => __('admin.Email'),
            'country_code' => __('admin.Country Code'),
            'contact_phone' => __('admin.Phone'),
            'country_id' => __('admin.Country'),
            'state_id' => __('admin.State'),
            'city_id' => __('admin.City'),
            'address' => __('admin.Address'),
            'google_map_url' => __('admin.Google Maps URL'),
            'linkedin_company_url' => __('admin.LinkedIn'),
            'twitter_handle' => __('admin.Twitter / X'),
            'facebook_page_url' => __('admin.Facebook'),
            'instagram_profile_url' => __('admin.Instagram'),
            'youtube_channel_url' => __('admin.YouTube'),
            'tiktok_url' => __('admin.TikTok'),
        ];
    }
}
