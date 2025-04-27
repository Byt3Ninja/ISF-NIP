<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
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