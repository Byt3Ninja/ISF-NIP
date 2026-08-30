<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
use Illuminate\Support\Facades\DB;
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
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',
            'email' => 'email|required|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('roles')
                        ->where('name', $value)
                        ->orWhere('default_for', $value)
                        ->exists();

                    if (!$exists) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                }
            ],
            'gender'=>'nullable|in:Male,Female',
            'img_url' => 'nullable|image|mimes:jpeg,bmp,png,jpg,gif',
            'country_code' => 'required',
            'bio_en'       => 'nullable|string',
            'Linkedin'     => 'nullable|url',
            'expertise'    => 'nullable|array',
            'expertise.*'  => 'exists:expertises,id',
            'title_en'     => 'nullable|string',
        ];

        // Apply mentor-required rules ONLY if role is Mentor (ID 6)
        if ($this->input('role') == 'Mentor') {
            $rules['bio_en']      = 'required|string';
            $rules['Linkedin']    = 'required|url';
            $rules['expertise']   = 'required|array|min:1';
            $rules['expertise.*'] = 'exists:expertises,id';
            $rules['title_en']    = 'required|string';
        }
        
        return $rules;
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
