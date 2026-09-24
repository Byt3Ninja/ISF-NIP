<?php

namespace App\Http\Requests;

use App\Services\TimezoneService;
use Carbon\Carbon;
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
            // GV-9566: the picker posts platform wall-clock; `after:now` would read it in the storage zone.
            'datetime' => ['bail', 'required', 'date', function ($attribute, $value, $fail) {
                $at = TimezoneService::wallClock($value);
                if (!$at || $at->lte(Carbon::now())) {
                    $fail($this->messages()['datetime.after']);
                }
            }],
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
