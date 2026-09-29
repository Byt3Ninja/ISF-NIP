<?php

namespace App\Http\Requests\Importer;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // 10 MB; extension/MIME checked by CsvFileValidator
        ];
    }
}
