<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;

class UpdatePermissionGroupRequest extends FormRequest
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
				'unique:permission_groups,name,' . $this->route('id'),
				'max:100'
			],
			'description' => [
				'sometimes',
				'nullable',
				'max:250'
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