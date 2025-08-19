<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class MoveToTrashRequest extends FormRequest
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

       protected function prepareForValidation()
    {
        if (is_numeric($this->messageIds)) {
            $this->merge([
                'messageIds' => [(int) $this->messageIds],
            ]);
        }
    }
    public function rules(): array
    {
        return [
            'messageIds'   => 'required|array|min:1',
            'messageIds.*' => 'integer|distinct'
        ];
    }

    public function messages(): array
{
    return [
        'messageIds.required'   => __('api.messageIds.required'),
        'messageIds.array'      => __('api.messageIds.array'),
        'messageIds.min'        => __('api.messageIds.min'),
        'messageIds.*.integer'  => __('api.messageIds.integer'),
        'messageIds.*.distinct' => __('api.messageIds.distinct'),
    ];
}

  protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(
                __('api.validation_error'),
                422,
                $validator->errors()
            )
        );
    }
}
