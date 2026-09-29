<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'       => 'required|exists:services,id',
            'datetime' => 'required|date|after:now',
            'comment'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        $isAr = app()->getLocale() === 'ar';

        return [
            'datetime.required' => $isAr
                ? 'يرجى اختيار التاريخ للمتابعة'
                : 'Please select a date to continue.',
            'datetime.after' => $isAr
                ? 'يجب أن يكون التاريخ في المستقبل'
                : 'The date must be in the future.',
            'comment.max' => $isAr
                ? 'يجب ألا تتجاوز الملاحظات 1000 حرف'
                : 'Notes must not exceed 1000 characters.',
        ];
    }
}
