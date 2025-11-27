<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name_en' => 'required',
            'description_en' => 'required',
            'img_url' => 'nullable',
            'service_category_id' => 'required|exists:service_categories,id',
            'vendor_name' => 'nullable|string|max:50',
            'vendor_email' => 'nullable|string|max:50|unique:services,vendor_email,except,id',
            'vendor_phone' => 'nullable|string|max:11',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'exists:organizations,id',
        ];

        if( auth()->check() )
        {
            $user = auth()->user();
            if( !$user->hasRole('serviceProvider') )
            {

                $rules['service_provider_id'] = 'required|exists:service_providers,id';
            }
        }
        return $rules;
    }
}
