<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponder;
class UpdateScheduleRequest extends FormRequest
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
            'days' => 'required|string|max:250',
            'status' => 'required|in:Available,Unavailable',
            'time_slots' => 'nullable|array',
            'time_slots.*.start' => 'required_with:time_slots|date_format:H:i:s',
            'time_slots.*.end' => 'required_with:time_slots|date_format:H:i:s|after:time_slots.*.start',
            'notes' => 'nullable|string|max:50',
            'status' => 'required|string|in:Available,Unavailable',
        ];
    }

      protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
