<?php

namespace App\Http\Requests\Importer;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'organization_id'   => ['required', 'integer', 'exists:organizations,id'],
            'accepted_warnings' => ['sometimes', 'boolean'],
        ];
    }
}
