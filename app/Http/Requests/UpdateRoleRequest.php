<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'permissions' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ];
    }
}
