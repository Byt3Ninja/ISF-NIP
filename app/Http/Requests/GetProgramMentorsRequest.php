<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class GetProgramMentorsRequest extends FormRequest
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
            'page' => 'integer|min:1|gt:0',
            'limit' => 'integer|min:1|max:100|gt:0',
            'search' => 'nullable|string',
            'sort' => 'nullable|in:rating,joinedAt,first_name_en,last_name_en',
            'order' => 'nullable|in:asc,desc',
        ];
    }

      protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                $this->validationError(__('api.invalid_query'), 422, $validator->errors())
            );
        }

        parent::failedValidation($validator);
    }
}
