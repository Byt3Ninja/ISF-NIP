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
            'image.required' => __('admin.Image is required'),
            'image.image' => __('admin.File must be an image'),
            'image.mimes' => __('admin.Image must be jpeg, png, jpg, gif, or webp'),
            'image.max' => __('admin.Image size must not exceed 5MB'),
            'cycle_id.required' => __('admin.Cycle ID is required'),
            'cycle_id.exists' => __('admin.Invalid cycle'),
        ];
    }
}
