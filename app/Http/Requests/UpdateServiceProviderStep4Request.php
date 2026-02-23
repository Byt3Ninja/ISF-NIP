<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceProviderStep4Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): bool|array
    {
        return [
            'portfolio_visibility' => 'required|string|in:on,off',
            'service_provider_id' => 'nullable|integer|exists:service_providers,id',
            'portfolio_description' => 'nullable|string|max:50',
            'portfolio' => 'nullable|array|max:20',
            'portfolio.*.id' => 'nullable|integer|exists:service_provider_portfolios,id',
            'portfolio.*.delete' => 'nullable|string|in:true,false',
            
            'portfolio.*.title' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $item = $this->input("portfolio.{$index}");
                    if (($item['delete'] ?? 'false') === 'true') return;

                    $hasMedia = !empty($item['id']) || $this->hasFile("portfolio.{$index}.media");
                    $hasDesc = !empty($item['description']);
                    if (($hasMedia || $hasDesc) && empty($value)) {
                        $fail(__('admin.complete_work_item_details'));
                    }
                }
            ],
            'portfolio.*.description' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $item = $this->input("portfolio.{$index}");
                    if (($item['delete'] ?? 'false') === 'true') return;

                    $hasMedia = !empty($item['id']) || $this->hasFile("portfolio.{$index}.media");
                    $hasTitle = !empty($item['title']);
                    if (($hasMedia || $hasTitle) && empty($value)) {
                        $fail(__('admin.complete_work_item_details'));
                    }
                }
            ],
            'portfolio.*.media' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp,mp4',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $item = $this->input("portfolio.{$index}");
                    if (($item['delete'] ?? 'false') === 'true') return;

                    $hasId = !empty($item['id']);
                    $hasTitle = !empty($item['title']);
                    $hasDesc = !empty($item['description']);
                    $hasNewFile = $this->hasFile($attribute);

                    if (($hasTitle || $hasDesc) && !$hasId && !$hasNewFile) {
                        $fail(__('admin.complete_work_item_details'));
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'portfolio.*.title.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.description.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.media.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.media.mimes' => __('admin.unsupported_file_type_portfolio') ?? 'Unsupported file type. Upload PNG, JPG, WEBP, or MP4.',
            'portfolio.*.media.max' => __('admin.file_size_too_large_portfolio') ?? 'File size must be 10MB or less.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'portfolio_visibility' => $this->portfolio_visibility ?? 'off',
        ]);
    }
}
