<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id'   => 'required|integer|exists:services_requests,id',
            'rating'       => 'required|integer|min:1|max:5',
            'review_title' => 'nullable|required_with:review_body|string|max:50',
            'review_body'  => 'nullable|required_with:review_title|string|max:500',
        ];
    }

    public function messages(): array
    {
        $isAr = app()->getLocale() === 'ar';

        return [
            'rating.required' => $isAr
                ? 'التقييم مطلوب.'
                : 'Rating is required.',
            'rating.min' => $isAr
                ? 'التقييم مطلوب.'
                : 'Rating is required.',
            'rating.max' => $isAr
                ? 'التقييم مطلوب.'
                : 'Rating is required.',
            'review_title.required_with' => $isAr
                ? 'عنوان التقييم مطلوب.'
                : 'Review title is required.',
            'review_title.max' => $isAr
                ? 'يجب ألا يتجاوز عنوان التقييم 50 حرفاً.'
                : 'Review title must not exceed 50 characters.',
            'review_body.required_with' => $isAr
                ? 'يرجى كتابة التقييم.'
                : 'Please write your review.',
            'review_body.max' => $isAr
                ? 'يجب ألا يتجاوز التقييم 500 حرف.'
                : 'Review must not exceed 500 characters.',
        ];
    }
}
