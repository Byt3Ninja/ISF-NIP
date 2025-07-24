<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Auth;

class StoreUpdateClientCompanyRequest extends FormRequest
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

        $companyId = Auth::user()->company_id;
        //make sure that, the ID is number
//        !is_numeric ($companyId)? abort('401'): '';
        return [
            'state_id' => ['sometimes', 'numeric'],
            'city_id' => ['sometimes', 'numeric'],
            'name_en' => ['sometimes', 'string'],
            'name_ar' => ['sometimes', 'string'],
            'address_en' => ['sometimes', 'string'],
            'address_ar' => ['sometimes', 'string'],
            'phone' => ['sometimes'],
            // 'email' => 'email|sometimes|unique:companies,email,'.$companyId,
        ];
    }
}
