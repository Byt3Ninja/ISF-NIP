<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreMessageRequest extends FormRequest
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
            'userTypeValue' => 'required|in:0,1,2,3',
            'subject'       => 'nullable|string',
            'message'         => 'required|string|max:255',
            'draft'         => 'boolean',
            'cycleId'       => 'nullable|integer|exists:cycles,id',
        ];
    }

    protected function prepareForValidation()
{
    $userId = $this->input('userId');

    if (!is_array($userId)) {
        $this->merge(['userId' => [$userId]]);
    }
}

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}
