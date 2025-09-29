<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;

class TrainingReportFilterRequest extends FormRequest
{
    use ApiResponder;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program' => 'nullable|string|exists:cycles,title',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ];
    }
}
