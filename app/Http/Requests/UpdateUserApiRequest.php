<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateUserApiRequest extends FormRequest
{
    use ApiResponder;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
    $userId = $this->route('user')?->id;

    return [
        'first_name' => ['nullable', 'string', 'max:255'],
        'last_name' => ['nullable', 'string', 'max:255'],
        'email' => [
            'nullable',
            'email',
            Rule::unique('users', 'email')->ignore($userId),
        ],
        'phone' => [
            'nullable',
            Rule::unique('users', 'phone')->ignore($userId),
        ],
        'gender' => ['nullable', 'string', 'in:Male,Female'],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        'status' => ['nullable', 'string'],
        'HaveCompany'=> ['nullable', 'bool'],
        'country_id'=> ['nullable','exists:countries,id'],
        'img_url'=> ['nullable'],
        'country_code' => 'nullable|numeric|digits_between:1,5',
        'state_id' => 'nullable|exists:states,id',
        'city_id' => 'nullable|exists:cities,id',
        'houre_rate' => 'nullable|numeric',
        'title_en' => 'nullable|string|max:100',
        'email_verified_at' => 'nullable|date',
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
