<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
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
        return [
            'name' => [
                'string',
                'required',
                'unique:permissions,name',
            ],
            'permission_group_id' => [
                'nullable',
                'sometimes',
                'exists:permission_groups,id',
            ],
            'is_core' => [
                'sometimes',
                'nullable',
            ]
        ];
    }
}
