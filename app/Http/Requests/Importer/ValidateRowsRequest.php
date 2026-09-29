<?php

namespace App\Http\Requests\Importer;

use Illuminate\Foundation\Http\FormRequest;

class ValidateRowsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'mappings'   => ['required', 'array'],
            'mappings.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
