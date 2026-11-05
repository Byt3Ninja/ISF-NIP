<?php

namespace App\Http\Requests;

use App\User;
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
       $user = Auth::user();
       $id = $user->id;

        $rules = [
            'first_name_en' => ['string', 'max:255'],
            'last_name_en' => ['string', 'max:255'],
            'title_en' => ['string', 'max:255'],
            'img_url' => 'nullable|mimes:jpeg,png,jpg',
            'phone' => ['string'],
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
            'country_id' => ['integer', 'exists:countries,id'],
            'state_id' => ['integer', 'exists:states,id'],
            'address' => ['string', 'max:100'],
            'timezone' => ['timezone'],
            'HaveCompany' => 'nullable|max:1',
            'technologies_used' => 'nullable|array',
            'technologies_used.*' => 'nullable|string|max:50',
            'should_remove_image' => 'nullable|sometimes|boolean',
        ];

        if ($user->hasAnyRole(['Registered', 'registered'])) {
            $rules['academic_stat'] = ['in:Graduate,Student'];
            $rules['dob']           = ['date', 'before:today'];
            $rules['national_id']   = ['alpha_num', 'max:50'];
        }

        // Required vs optional comes from User::PROFILE_REQUIRED_FIELDS, the same list the
        // form, the client validator and hasCompletedProfile() read.
        $required = $user->requiredProfileFields();
        foreach (array_keys(User::PROFILE_REQUIRED_FIELDS) as $field) {
            if (isset($rules[$field])) {
                array_unshift($rules[$field], in_array($field, $required, true) ? 'required' : 'nullable');
            }
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

    /**
     * Field labels for the default messages, matching what the form shows.
     */
    public function attributes()
    {
        return [
            'first_name_en' => __('admin.First Name'),
            'last_name_en'  => __('admin.Last Name'),
            'title_en'      => __('admin.JobTitleEn'),
            'phone'         => __('admin.Phone'),
            'state_id'      => __('admin.State'),
            'address'       => __('admin.Address'),
            'timezone'      => __('admin.Timezone'),
        ];
    }
}
