<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name_en' => 'required|string|max:255',
            'last_name_en' => 'required|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'gender' => 'required|in:Male,Female',
            'email' => 'required|email|max:255|unique:users,email',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).+$/'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return [
                'first_name_en.required' => 'يرجى إدخال الاسم الأول',
                'first_name_en.max' => 'الاسم الأول يجب أن لا يتجاوز 255 حرفاً',
                'last_name_en.required' => 'يرجى إدخال اسم العائلة',
                'last_name_en.max' => 'اسم العائلة يجب أن لا يتجاوز 255 حرفاً',
                'first_name_ar.max' => 'الاسم الأول يجب أن لا يتجاوز 255 حرفاً',
                'last_name_ar.max' => 'اسم العائلة يجب أن لا يتجاوز 255 حرفاً',
                'gender.required' => 'يرجى اختيار النوع',
                'gender.in' => 'النوع المحدد غير صالح',
                'email.required' => 'يرجى إدخال البريد الإلكتروني',
                'email.email' => 'يرجى إدخال بريد إلكتروني صالح',
                'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل',
                'email.max' => 'البريد الإلكتروني يجب أن لا يتجاوز 255 حرفاً',
                'country_code.required' => 'يرجى اختيار رمز الدولة',
                'phone.required' => 'يرجى إدخال رقم الهاتف',
                'phone.unique' => 'رقم الهاتف مستخدم بالفعل',
                'phone.max' => 'رقم الهاتف يجب أن لا يتجاوز 20 رقماً',
                'password.required' => 'يرجى إدخال كلمة المرور',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                'password.confirmed' => 'كلمتا المرور غير متطابقتين',
                'password.regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير وحرف صغير ورقم ورمز خاص',
            ];
        }

        return [
            'first_name_en.required' => 'Please enter the first name.',
            'first_name_en.max' => 'First name must not exceed 255 characters.',
            'last_name_en.required' => 'Please enter the last name.',
            'last_name_en.max' => 'Last name must not exceed 255 characters.',
            'first_name_ar.max' => 'First name must not exceed 255 characters.',
            'last_name_ar.max' => 'Last name must not exceed 255 characters.',
            'gender.required' => 'Please select the gender.',
            'gender.in' => 'The selected gender is invalid.',
            'email.required' => 'Please enter the email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',
            'email.max' => 'Email must not exceed 255 characters.',
            'country_code.required' => 'Please select the country code.',
            'phone.required' => 'Please enter the phone number.',
            'phone.unique' => 'This phone number is already in use.',
            'phone.max' => 'Phone number must not exceed 20 digits.',
            'password.required' => 'Please enter the password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number and special character.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name_en' => __('admin.First Name (English)'),
            'last_name_en' => __('admin.Last Name (English)'),
            'first_name_ar' => __('admin.First Name (Arabic)'),
            'last_name_ar' => __('admin.Last Name (Arabic)'),
            'gender' => __('admin.Gender'),
            'email' => __('admin.Email'),
            'country_code' => __('admin.Country Code'),
            'phone' => __('admin.Phone'),
            'password' => __('admin.Password'),
        ];
    }
}
