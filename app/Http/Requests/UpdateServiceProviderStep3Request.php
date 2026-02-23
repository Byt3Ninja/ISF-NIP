<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceProviderStep3Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'stats_visibility' => 'required|string|in:on,off',
            'service_provider_id' => 'nullable|integer|exists:service_providers,id',
        ];

        for ($i = 1; $i <= 4; $i++) {
            $rules["value{$i}"] = [
                'nullable',
                'string',
                'max:20',
                "required_with:label{$i}",
            ];
            $rules["label{$i}"] = [
                'nullable',
                'string',
                'max:50',
                "required_with:value{$i}",
            ];
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        for ($i = 1; $i <= 4; $i++) {
            $messages["value{$i}.required_with"] = __('admin.Value is required when a label is provided');
            $messages["label{$i}.required_with"] = __('admin.Label is required when a value is provided');
        }
        return $messages;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'stats_visibility' => $this->has('stats_visibility') ? 'on' : 'off',
        ]);
    }
}
