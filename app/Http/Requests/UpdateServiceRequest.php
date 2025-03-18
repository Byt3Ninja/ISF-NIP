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
            'service_category_id' => 'required|exists:service_categories,id',
            'vendor_name' => 'required|string|max:50',
            'vendor_email' => [
                'required',
                'string',
                'max:50',
                Rule::unique('services', 'vendor_email')->ignore($this->route('service')),
            ],
            'vendor_phone' => 'required|string|max:11',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'exists:organizations,id',
        ];
        if( UpdateServiceRequest::hasFile('img_url') ) { $rules['img_url'] = 'required'; }
        return $rules;
    }
}
