<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreMentorshipSessionRequest extends FormRequest
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
            'mentor_id' => 'required|exists:users,id',
            'preferred_date_time' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|in:30,45,60',
            'override_availability' => 'boolean',
            'override_reason' => 'required_if:override_availability,true|string|max:100',
            'notes' => 'nullable|string',
            'cycle_id' => 'required|exists:cycles,id', 
            'organization_id' => 'required|exists:organizations,id', 
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
