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
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $item = $this->input("portfolio.{$index}");
                    if (($item['delete'] ?? 'false') === 'true') return;

                    $normalized = str_replace(["\r\n", "\r"], "\n", (string) $value);
                    if (mb_strlen($normalized) > 100) {
                        $fail(__('validation.max.string', ['attribute' => __('validation.attributes.description') ?: 'description', 'max' => 100]));
                        return;
                    }

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
                    if (!$value instanceof \Illuminate\Http\UploadedFile) return;

                    // Block dangerous file extensions
                    $dangerous = ['php','php3','php4','php5','phtml','cgi','sh','bash','bin','pl','htaccess','htpasswd','ksh'];
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (in_array($ext, $dangerous)) {
                        $fail(__('admin.unsupported_file_type_portfolio'));
                        return;
                    }

                    // Verify actual MIME type (prevents renamed malicious files)
                    $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'video/mp4'];
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail(__('admin.unsupported_file_type_portfolio'));
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $portfolio = $this->input('portfolio', []);
            foreach ($portfolio as $index => $item) {
                if (($item['delete'] ?? 'false') === 'true') continue;

                $hasId = !empty($item['id']);
                $hasTitle = !empty($item['title']);
                $hasDesc = !empty($item['description']);
                $hasNewFile = $this->hasFile("portfolio.{$index}.media");

                if (($hasTitle || $hasDesc) && !$hasId && !$hasNewFile) {
                    $validator->errors()->add(
                        "portfolio.{$index}.media",
                        __('admin.complete_work_item_details') ?? 'Complete the work item details.'
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'portfolio.*.id.exists' => __('admin.invalid_work_item') ?? 'This work item is invalid or no longer exists.',
            'portfolio.*.title.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.description.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.media.required_with' => __('admin.complete_work_item_details') ?? 'Complete the work item details.',
            'portfolio.*.media.mimes' => __('admin.unsupported_file_type_portfolio') ?? 'Unsupported file type. Upload PNG, JPG, WEBP, or MP4.',
            'portfolio.*.media.max' => __('admin.file_size_too_large_portfolio') ?? 'File size must be 10MB or less.',
        ];
    }

    protected function prepareForValidation()
    {
        $portfolio = $this->input('portfolio');
        if (is_array($portfolio)) {
            foreach ($portfolio as $i => $item) {
                if (isset($item['description']) && is_string($item['description'])) {
                    $portfolio[$i]['description'] = str_replace(["\r\n", "\r"], "\n", $item['description']);
                }
            }
        }

        $this->merge([
            'portfolio_visibility' => $this->portfolio_visibility ?? 'off',
            'portfolio' => $portfolio,
        ]);
    }
}
