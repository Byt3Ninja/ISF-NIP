<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationStep5Request extends FormRequest
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
            'custom_sections' => 'nullable|array|max:10',
            'custom_sections.*.id' => 'nullable|integer',
            'custom_sections.*.title' => 'required_with:custom_sections.*.content,custom_sections.*.visibility|nullable|string|max:255',
            'custom_sections.*.content' => 'required_with:custom_sections.*.title,custom_sections.*.visibility|nullable|string|max:10000',
            'custom_sections.*.visibility' => 'required_with:custom_sections.*.title,custom_sections.*.content|nullable|in:public,participants,admins_only',
            'custom_sections.*.bg_type' => 'nullable|in:solid,gradient',
            'custom_sections.*.bg_color' => 'nullable|string|max:20',
            'custom_sections.*.bg_gradient_start' => 'nullable|string|max:20',
            'custom_sections.*.bg_gradient_end' => 'nullable|string|max:20',
            'custom_sections.*.sort_order' => 'nullable|integer',
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
                'custom_sections.max' => 'الحد الأقصى للأقسام المخصصة هو 10',
                'custom_sections.*.title.required_with' => 'يرجى إكمال جميع الحقول الخاصة بهذا القسم',
                'custom_sections.*.title.max' => 'يجب ألا يتجاوز عنوان القسم 255 حرفاً',
                'custom_sections.*.content.required_with' => 'يرجى إكمال جميع الحقول الخاصة بهذا القسم',
                'custom_sections.*.content.max' => 'يجب ألا يتجاوز محتوى القسم 10,000 حرف',
                'custom_sections.*.visibility.required_with' => 'يرجى اختيار خيار الظهور',
                'custom_sections.*.visibility.in' => 'يرجى اختيار خيار ظهور صالح',
                'custom_sections.*.bg_type.in' => 'يرجى اختيار نوع خلفية صالح',
            ];
        }

        return [
            'custom_sections.max' => 'Maximum 10 custom sections allowed',
            'custom_sections.*.title.required_with' => 'Please complete all fields for this custom section',
            'custom_sections.*.title.max' => 'Section title must not exceed 255 characters',
            'custom_sections.*.content.required_with' => 'Please complete all fields for this custom section',
            'custom_sections.*.content.max' => 'Section content must not exceed 10,000 characters',
            'custom_sections.*.visibility.required_with' => 'Please select the visibility option',
            'custom_sections.*.visibility.in' => 'Please select a valid visibility option',
            'custom_sections.*.bg_type.in' => 'Please select a valid background type',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sections = $this->input('custom_sections', []);

            foreach ($sections as $index => $section) {
                // Check if any field is filled
                $hasTitle = !empty($section['title']);
                $hasContent = !empty($section['content']);
                $hasVisibility = !empty($section['visibility']);

                // If any of the main fields is filled, all must be filled
                if ($hasTitle || $hasContent || $hasVisibility) {
                    if (!$hasTitle) {
                        $validator->errors()->add(
                            "custom_sections.{$index}.title",
                            $this->getConditionalMessage('title')
                        );
                    }
                    if (!$hasContent) {
                        $validator->errors()->add(
                            "custom_sections.{$index}.content",
                            $this->getConditionalMessage('content')
                        );
                    }
                    if (!$hasVisibility) {
                        $validator->errors()->add(
                            "custom_sections.{$index}.visibility",
                            $this->getConditionalMessage('visibility')
                        );
                    }
                }
            }
        });
    }

    /**
     * Get conditional validation message.
     *
     * @param string $field
     * @return string
     */
    protected function getConditionalMessage(string $field): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            if ($field === 'visibility') {
                return 'يرجى اختيار خيار الظهور';
            }
            return 'يرجى إكمال جميع الحقول الخاصة بهذا القسم';
        }

        if ($field === 'visibility') {
            return 'Please select the visibility option';
        }
        return 'Please complete all fields for this custom section';
    }
}
