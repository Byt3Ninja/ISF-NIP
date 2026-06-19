<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCycleCustomPageImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() &&
               auth()->user()->ableToAny(['programs.create', 'programs.edit']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'cycle_id' => 'required|exists:cycles,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.required' => __('admin.file_image_required_validation'),
            'image.image' => __('admin.file_image_type_validation'),
            'image.mimes' => __('admin.file_mimes_validation'),
            'image.max' => __('admin.file_size_validation'),
            'cycle_id.required' => __('admin.cycle_required_validation'),
            'cycle_id.exists' => __('admin.cycle_exists_validation'),
        ];
    }
}
