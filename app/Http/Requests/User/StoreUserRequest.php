<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class StoreUserRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        

        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',
            'email' => 'email|required|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'img_url' => 'nullable|image|mimes:jpeg,bmp,png,jpg,gif',
            'country_code' => 'required|numeric|digits_between:1,5',
            'bio_en'       => 'nullable|string',
            'Linkedin'     => 'nullable|url',
            'expertise'    => 'nullable|array',
            'expertise.*'  => 'exists:expertises,id',
            'title_en'     => 'nullable|string',
        ];

    // Apply mentor-required rules ONLY if:
    // - role is "Mentor"
    // - auth user is NOT an Admin
    if ($this->input('role') === 'Mentor' && !auth()->user()->hasRole('Admin')) {
        $rules['bio_en']      = 'required|string';
        $rules['Linkedin']    = 'required|url';
        $rules['expertise']   = 'required|array';
        $rules['expertise.*'] = 'exists:expertises,id';
        $rules['title_en']    = 'required|string';
    }
    
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
