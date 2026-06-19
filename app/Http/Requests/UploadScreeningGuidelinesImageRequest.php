<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadScreeningGuidelinesImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->ableToAny(['screening.update', 'screening.store']);
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
            'step_id' => 'required|exists:steps,id',
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
            'step_id.required' => __('admin.step_required_validation'),
            'step_id.exists' => __('admin.step_exists_validation'),
        ];
    }
}
