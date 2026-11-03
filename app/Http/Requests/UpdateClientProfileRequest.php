<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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

        $rules = [
            'first_name_en' => 'required|string|max:255',
            'last_name_en' => 'required|string|max:255',
            'img_url' => 'nullable|mimes:jpeg,png,jpg',
            'phone' => 'required',
            'email' => [
                            'required',
                            'email',
                            'unique:users,email,' . $id,
                            // Also reject an address another account has already staged as
                            // its pending change, otherwise two users race for it and
                            // whoever confirms last wins.
                            Rule::unique('users', 'new_email')->ignore($id),
                            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                        ],
            'country_id' => 'required|integer|exists:countries,id',
            'HaveCompany' => 'nullable|max:1',
            'technologies_used' => 'nullable|array',
            'technologies_used.*' => 'nullable|string|max:50',
            'should_remove_image' => 'nullable|sometimes|boolean',
        ];

        if (Auth::user()->hasAnyRole(['Registered', 'registered'])) {
            $rules['academic_stat'] = 'required|in:Graduate,Student';
            $rules['dob']           = 'required|date|before:today';
            $rules['national_id']   = (config('app.national_id_required') ? 'required' : 'nullable') . '|alpha_num|max:50';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'country_id.required'   => __('admin.this_field_is_required'),
            'academic_stat.required'=> __('admin.this_field_is_required'),
            'academic_stat.in'      => __('admin.this_field_is_required'),
            'dob.required'          => __('admin.this_field_is_required'),
            'dob.date'              => __('admin.dob_future_not_allowed'),
            'dob.before'            => __('admin.dob_future_not_allowed'),
            'national_id.required'  => __('admin.national_id_is_required'),
            'national_id.alpha_num' => __('admin.invalid_national_id'),
            'national_id.max'       => __('admin.invalid_national_id'),
        ];
    }
}
