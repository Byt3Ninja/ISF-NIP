<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceProviderStep2Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'hero_description' => 'required|string|max:100',
            'services_short_description' => 'required|string|max:200',
            'appointment_link' => 'nullable|url|max:500',
            'operating_mode' => 'required|string|in:online_remote,on_site,online_on_site',
            'operating_region' => 'required|string|in:global,mena,gcc,africa,europe,north_america,asia',
            'service_provider_id' => 'nullable|integer|exists:service_providers,id',
        ];
    }
    
    public function messages()
    {
        return [
            'hero_image.mimes' => __('admin.hero_image_unsupported'),
            'hero_image.max' => __('admin.hero_image_too_large'),
            'appointment_link.url' => __('admin.invalid_url'),
            'hero_description.required' => __('admin.required'),
            'services_short_description.required' => __('admin.required'),
            'operating_mode.required' => __('admin.required'),
            'operating_region.required' => __('admin.required'),
        ];
    }
}
