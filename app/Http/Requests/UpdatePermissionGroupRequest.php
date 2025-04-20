<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UpdatePermissionGroupRequest extends FormRequest
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
				'unique:permission_groups,name,' . request()->permission_group->id
			],
			'description' => [
				'sometimes',
				'nullable',
			]
		];
	}
}