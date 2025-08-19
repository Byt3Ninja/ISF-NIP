<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',
            'email' => 'email|required|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'img_url' => 'nullable|image|mimes:jpeg,bmp,png,jpg,gif',
            'country_code' => 'required',
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

}
