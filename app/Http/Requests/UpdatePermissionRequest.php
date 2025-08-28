<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdatePermissionRequest extends FormRequest
{
    use ApiResponder;
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'name' => [
                'string',
                'sometimes',
                'unique:permissions,name,' . $this->route('permission'),
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

     protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
         );
      }

        parent::failedValidation($validator);
    }
}