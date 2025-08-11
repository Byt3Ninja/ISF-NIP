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
        return false;
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
            'bio_en' => 'required_if:role,Mentor',
            'Linkedin' => 'required_if:role,Mentor|url',
            'expertise' => 'required_if:role,Mentor|array',
            'expertise.*' => 'exists:expertises,id',
            'country_code' => 'required',
            'title_en' => 'required_if:role,Mentor|string',

        ];


    }


}
