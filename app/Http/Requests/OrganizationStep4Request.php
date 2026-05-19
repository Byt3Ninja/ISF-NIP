<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStep4Request extends FormRequest
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
    public function rules(): array
    {
        return [
            // Landing Page Enabled
            'landing_enabled' => 'nullable|boolean',

            // Slug
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:organizations,slug,' . $this->route('id'),
            ],

            // How It Works Section
            'how_it_works_enabled' => 'nullable|boolean',
            'how_it_works_title' => 'nullable|string|max:255',
            'how_it_works_subtitle' => 'nullable|string|max:255',
            'how_it_works_description' => 'nullable|string|max:1000',
            'process_steps' => 'nullable|array|max:4',
            'process_steps.*.title' => 'nullable|string|max:255',
            'process_steps.*.description' => 'nullable|string|max:1000',
            'process_steps.*.icon' => 'nullable|string|max:50',

            // Achievements Section
            'achievements_enabled' => 'nullable|boolean',
            'achievements_title' => 'nullable|string|max:255',
            'achievements_subtitle' => 'nullable|string|max:255',
            'achievements_description' => 'nullable|string|max:1000',
            'metrics' => 'nullable|array|max:8',
            'metrics.*.number' => 'nullable|string|max:20',
            'metrics.*.label' => 'nullable|string|max:255',
            'metrics.*.icon' => 'nullable|string|max:50',

            // Media & Recognition Section
            'media_enabled' => 'nullable|boolean',
            'media_title' => 'nullable|string|max:255',
            'media_subtitle' => 'nullable|string|max:255',
            'media_description' => 'nullable|string|max:1000',
            'articles' => 'nullable|array|max:8',
            'articles.*.publication_name' => 'nullable|string|max:255',
            'articles.*.date' => 'nullable|date',
            'articles.*.title' => 'nullable|string|max:255',
            'articles.*.summary' => 'nullable|string|max:1000',
            'articles.*.link' => 'nullable|url|max:500',
            'articles.*.image' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:5120',
            'articles.*.existing_image' => 'nullable|string',

            // Partners Section
            'partners_enabled' => 'nullable|boolean',
            'partners_title' => 'nullable|string|max:255',
            'partners_subtitle' => 'nullable|string|max:255',
            'partners_description' => 'nullable|string|max:1000',
            'partners_list' => 'nullable|array|max:20',
            'partners_list.*.name' => 'nullable|string|max:255',
            'partners_list.*.logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'partners_list.*.existing_logo' => 'nullable|string',

            // Section Type (for tab-isolated saves)
            'section_type' => 'nullable|string|in:howItWorks,achievements,media,partners,programs,url',

            // Theme
            'landing_theme' => 'nullable|in:gradient,minimal,vuexy',

            // Section Backgrounds
            'hero_bg_type' => 'nullable|in:solid,gradient',
            'hero_bg_color' => 'nullable|string|max:20',
            'hero_bg_gradient_start' => 'nullable|string|max:20',
            'hero_bg_gradient_end' => 'nullable|string|max:20',
            'how_it_works_bg_type' => 'nullable|in:solid,gradient',
            'how_it_works_bg_color' => 'nullable|string|max:20',
            'how_it_works_bg_gradient_start' => 'nullable|string|max:20',
            'how_it_works_bg_gradient_end' => 'nullable|string|max:20',
            'achievements_bg_type' => 'nullable|in:solid,gradient',
            'achievements_bg_color' => 'nullable|string|max:20',
            'achievements_bg_gradient_start' => 'nullable|string|max:20',
            'achievements_bg_gradient_end' => 'nullable|string|max:20',
            'media_bg_type' => 'nullable|in:solid,gradient',
            'media_bg_color' => 'nullable|string|max:20',
            'media_bg_gradient_start' => 'nullable|string|max:20',
            'media_bg_gradient_end' => 'nullable|string|max:20',
            'partners_bg_type' => 'nullable|in:solid,gradient',
            'partners_bg_color' => 'nullable|string|max:20',
            'partners_bg_gradient_start' => 'nullable|string|max:20',
            'partners_bg_gradient_end' => 'nullable|string|max:20',

            // Programs Section
            'programs_enabled' => 'nullable|boolean',
            'programs_title' => 'nullable|string|max:255',
            'programs_subtitle' => 'nullable|string|max:255',
            'programs_description' => 'nullable|string|max:1000',
            'programs_order_by' => 'nullable|in:newest,oldest,custom',
            'programs_visibility' => 'nullable|array',
            'programs_visibility.*' => 'nullable|integer',
            'programs_bg_type' => 'nullable|in:solid,gradient',
            'programs_bg_color' => 'nullable|string|max:20',
            'programs_bg_gradient_start' => 'nullable|string|max:20',
            'programs_bg_gradient_end' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            return [
                'slug.regex' => 'يجب أن يحتوي الرابط على أحرف إنجليزية صغيرة وأرقام وشرطات فقط',
                'slug.unique' => 'هذا الرابط مستخدم بالفعل',
                'process_steps.max' => 'الحد الأقصى لخطوات العملية هو 4',
                'metrics.max' => 'الحد الأقصى للمؤشرات هو 8',
                'articles.max' => 'الحد الأقصى للمقالات هو 8',
                'partners_list.max' => 'الحد الأقصى للشركاء هو 20',
                'process_steps.*.title.max' => 'عنوان الخطوة يجب أن لا يتجاوز 255 حرف',
                'process_steps.*.description.max' => 'وصف الخطوة يجب أن لا يتجاوز 1000 حرف',
                'metrics.*.number.max' => 'رقم المؤشر يجب أن لا يتجاوز 20 حرف',
                'metrics.*.label.max' => 'تسمية المؤشر يجب أن لا يتجاوز 255 حرف',
                'articles.*.publication_name.max' => 'اسم المنشور يجب أن لا يتجاوز 255 حرف',
                'articles.*.title.max' => 'عنوان المقال يجب أن لا يتجاوز 255 حرف',
                'articles.*.summary.max' => 'ملخص المقال يجب أن لا يتجاوز 1000 حرف',
                'articles.*.link.url' => 'يرجى إدخال رابط صالح',
                'articles.*.image.image' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG أو SVG أو WebP فقط',
                'articles.*.image.mimes' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG أو SVG أو WebP فقط',
                'articles.*.image.max' => 'الملف اكبر من الحد المسموح. الحد الأقصى المسموح به هو 5 ميجابايت',
                'partners_list.*.name.max' => 'اسم الشريك يجب أن لا يتجاوز 255 حرف',
                'partners_list.*.logo.image' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG أو SVG أو WebP فقط',
                'partners_list.*.logo.mimes' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG أو SVG أو WebP فقط',
                'partners_list.*.logo.max' => 'الملف أكبر من الحد المسموح به. الحد الأقصى المسموح به هو 2 ميجابايت',
                'how_it_works_description.max' => 'الوصف يجب أن لا يتجاوز 1000 حرف',
                'achievements_description.max' => 'الوصف يجب أن لا يتجاوز 1000 حرف',
                'media_description.max' => 'الوصف يجب أن لا يتجاوز 1000 حرف',
                'partners_description.max' => 'الوصف يجب أن لا يتجاوز 1000 حرف',
                'landing_theme.in' => 'يرجى اختيار قالب صالح',
                'programs_description.max' => 'الوصف يجب أن لا يتجاوز 1000 حرف',
                'programs_order_by.in' => 'يرجى اختيار ترتيب صالح',
            ];
        }

        return [
            'slug.regex' => 'URL must contain only lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This URL is already in use',
            'process_steps.max' => 'Maximum 4 process steps allowed',
            'metrics.max' => 'Maximum 8 metrics allowed',
            'articles.max' => 'Maximum 8 articles allowed',
            'partners_list.max' => 'Maximum 20 partners allowed',
            'process_steps.*.title.max' => 'Step title must not exceed 255 characters',
            'process_steps.*.description.max' => 'Step description must not exceed 1000 characters',
            'metrics.*.number.max' => 'Metric number must not exceed 20 characters',
            'metrics.*.label.max' => 'Metric label must not exceed 255 characters',
            'articles.*.publication_name.max' => 'Publication name must not exceed 255 characters',
            'articles.*.title.max' => 'Article title must not exceed 255 characters',
            'articles.*.summary.max' => 'Article summary must not exceed 1000 characters',
            'articles.*.link.url' => 'Please enter a valid URL',
            'articles.*.image.image' => 'Invalid file format. Please upload PNG, JPG, SVG or WebP files only.',
            'articles.*.image.mimes' => 'Invalid file format. Please upload PNG, JPG, SVG or WebP files only.',
            'articles.*.image.max' => 'File too large. Maximum size allowed is 5MB.',
            'partners_list.*.name.max' => 'Partner name must not exceed 255 characters',
            'partners_list.*.logo.image' => 'Invalid file format. Please upload PNG, JPG, SVG or WebP files only.',
            'partners_list.*.logo.mimes' => 'Invalid file format. Please upload PNG, JPG, SVG or WebP files only.',
            'partners_list.*.logo.max' => 'File too large. Maximum size allowed is 2MB.',
            'how_it_works_description.max' => 'Description must not exceed 1000 characters',
            'achievements_description.max' => 'Description must not exceed 1000 characters',
            'media_description.max' => 'Description must not exceed 1000 characters',
            'partners_description.max' => 'Description must not exceed 1000 characters',
            'landing_theme.in' => 'Please select a valid theme',
            'programs_description.max' => 'Description must not exceed 1000 characters',
            'programs_order_by.in' => 'Please select a valid ordering option',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $sectionType = $this->input('section_type');

        $checkboxMap = [
            'howItWorks'   => ['how_it_works_enabled'],
            'achievements' => ['achievements_enabled'],
            'media'        => ['media_enabled'],
            'partners'     => ['partners_enabled'],
            'programs'     => ['programs_enabled'],
        ];

        // Always include landing_enabled
        $fields = ['landing_enabled' => $this->has('landing_enabled')];

        // Only convert checkboxes for the current section (or all if no section_type)
        $sectionsToProcess = $sectionType ? [$sectionType] : array_keys($checkboxMap);
        foreach ($sectionsToProcess as $section) {
            if (!isset($checkboxMap[$section])) {
                continue;
            }
            foreach ($checkboxMap[$section] as $field) {
                $fields[$field] = $this->has($field);
            }
        }

        $this->merge($fields);
    }
}
