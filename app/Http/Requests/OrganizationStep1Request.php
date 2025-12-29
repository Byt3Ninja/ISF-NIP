<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStep1Request extends FormRequest
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
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'organization_type_id' => 'required|exists:organization_types,id',
            'status' => 'required|boolean',
            'bio' => 'required|string|max:1000',
            'industries' => 'nullable|array',
            'industries.*' => 'exists:static_categories,id',
            'img_url' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
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
                'name_en.required' => 'يرجى إدخال اسم المؤسسة',
                'name_en.max' => 'اسم المؤسسة يجب أن لا يتجاوز 255 حرفاً',
                'name_ar.required' => 'يرجى إدخال اسم المؤسسة',
                'name_ar.max' => 'اسم المؤسسة يجب أن لا يتجاوز 255 حرفاً',
                'organization_type_id.required' => 'يرجى اختيار نوع المؤسسة',
                'organization_type_id.exists' => 'نوع المؤسسة المحدد غير صالح',
                'status.required' => 'يرجى اختيار الحالة',
                'bio.required' => 'يرجى إدخال وصف المؤسسة',
                'bio.max' => 'وصف المؤسسة يجب أن لا يتجاوز 1000 حرف',
                'img_url.image' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.mimes' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.max' => 'الملف اكبر من الحد المسموح. الحد الأقصى المسموح به هو 5 ميجابايت',
            ];
        }

        return [
            'name_en.required' => 'Please enter the organization name.',
            'name_en.max' => 'Organization name must not exceed 255 characters.',
            'name_ar.required' => 'Please enter the organization name.',
            'name_ar.max' => 'Organization name must not exceed 255 characters.',
            'organization_type_id.required' => 'Please select the organization type.',
            'organization_type_id.exists' => 'The selected organization type is invalid.',
            'status.required' => 'Please select the status.',
            'bio.required' => 'Please enter the organization description.',
            'bio.max' => 'Organization description must not exceed 1000 characters.',
            'img_url.image' => 'Invalid file format. Please upload PNG or JPG files only.',
            'img_url.mimes' => 'Invalid file format. Please upload PNG or JPG files only.',
            'img_url.max' => 'File too large. Maximum size allowed is 5MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name_en' => __('admin.Organization Name (English)'),
            'name_ar' => __('admin.Organization Name (Arabic)'),
            'organization_type_id' => __('admin.Organization Type'),
            'status' => __('admin.Status'),
            'bio' => __('admin.Organization Description'),
            'industries' => __('admin.Industry Focus'),
            'img_url' => __('admin.Organization Logo'),
        ];
    }
}
