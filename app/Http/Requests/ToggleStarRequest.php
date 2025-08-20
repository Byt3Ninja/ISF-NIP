<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ToggleStarRequest extends FormRequest
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
            'messageIds' => 'required',
            'starred'   => 'required|boolean',
        ];
    }
     public function prepareForValidation(): void
    {
        if ($this->has('messageIds') && !is_array($this->messageIds)) {
            $this->merge([
                'messageIds' => is_numeric($this->messageIds)
                    ? [(int) $this->messageIds]
                    : explode(',', $this->messageIds)
            ]);
        }
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
