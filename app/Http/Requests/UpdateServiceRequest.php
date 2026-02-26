<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'price' => 'nullable|numeric|min:0|max:9999999.99|regex:/^\d{1,7}(\.\d{1,2})?$/',
            'status' => 'nullable|boolean',
            'service_category_id' => 'required|exists:service_categories,id',
            'vendor_name' => 'nullable|string|max:50',
            'vendor_email' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('services', 'vendor_email')->ignore($this->route('service')),
            ],
            'vendor_phone' => 'nullable|string|max:11',
            'organization_ids' => 'required|array',
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
        if( UpdateServiceRequest::hasFile('img_url') ) { $rules['img_url'] = 'required'; }
        return $rules;
    }
}
