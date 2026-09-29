<?php

namespace App\Http\Requests\Importer;

use Illuminate\Foundation\Http\FormRequest;

class DetectConflictsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'accept'          => ['sometimes', 'boolean'],
        ];
    }
}
