<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRoleRequest extends FormRequest
{
    use ApiResponder;
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
        $roleId = $this->route('roleId');

        return [
            'name' => 'sometimes|required|string|min:2|max:100|regex:/^[A-Za-z0-9 _-]+$/|unique:roles,name,' . $roleId,
            'permissions' => 'sometimes|array|min:1',
            'permissions.*' => 'array|min:1',
            'permissions.*.*' => 'string|exists:permissions,name',
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
