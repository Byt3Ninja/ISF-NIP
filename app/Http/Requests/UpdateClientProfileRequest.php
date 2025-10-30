<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UpdateClientProfileRequest extends FormRequest
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
       $id = Auth::user()->id;

        return [
            'first_name_en' => 'required|string|max:255',
            'last_name_en' => 'required|string|max:255',
            'img_url' => 'nullable|mimes:jpeg,png,jpg',
            'phone' => 'required',
            'email' => [
                            'required',
                            'email',
                            'unique:users,email,' . $id,
                            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                        ],
            'HaveCompany' => 'nullable|max:1',
            'technologies_used' => 'nullable|array',
            'technologies_used.*' => 'nullable|string|max:50',
        ];
    }
}
