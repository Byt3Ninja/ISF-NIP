<?php

namespace App\Http\Requests;

use App\Role;
use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class StoreRoleRequest extends FormRequest
{
    use ApiResponder;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:100|regex:/^[A-Za-z0-9 _-]+$/|unique:roles,name',
            'default_for' => ['nullable', 'string', Rule::in(Role::DEFAULT_FOR_VALUES)],
            'permissions' => 'required|array|min:1',
            'permissions_flat' => 'required|array|min:1',
            'permissions_flat.*' => 'string|exists:permissions,name',
        ];
    }

     protected function prepareForValidation(): void
    {
        $flattened = [];

        $permissions = $this->input('permissions', []);

        if ($this->isAssoc($permissions)) {
            foreach ($permissions as $group => $perms) {
                if (is_array($perms)) {
                    foreach ($perms as $perm) {
                        $flattened[] = $perm;
                    }
                }
            }
        } else {
            $flattened = $permissions;
        }

        $this->merge([
            'permissions_flat' => $flattened,
        ]);
    }

    protected function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
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
