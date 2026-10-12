<?php

namespace App\Http\Requests;

use App\Services\PlatformBrandingService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Platform Branding (GV-9674). The gradient colors are optional as a pair:
 * both empty = theme gradient, one without the other = blocked.
 */
class UpdatePlatformBrandingRequest extends FormRequest
{
    private const HEX = 'regex:/^#[0-9a-f]{6}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Accept "#ABC", "abc" etc. and validate the canonical "#aabbcc". A value
     * that isn't a color is left as typed so the regex rule rejects it.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];
        foreach (array_keys(PlatformBrandingService::FIELDS) as $field) {
            $value = $this->input('branding_' . $field);
            $normalized['branding_' . $field] = PlatformBrandingService::normalize($value) ?? $value;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'branding_gradient_start' => ['nullable', 'required_with:branding_gradient_end', self::HEX],
            'branding_gradient_end' => ['nullable', 'required_with:branding_gradient_start', self::HEX],
            'branding_main_color' => ['required', self::HEX],
            'branding_button_text_color' => ['required', self::HEX],
        ];
    }

    public function messages(): array
    {
        return [
            'branding_gradient_start.required_with' => __('settings.branding_enter_start_color'),
            'branding_gradient_start.regex' => __('settings.branding_valid_color'),
            'branding_gradient_end.required_with' => __('settings.branding_enter_end_color'),
            'branding_gradient_end.regex' => __('settings.branding_valid_color'),
            'branding_main_color.required' => __('settings.branding_valid_main_color'),
            'branding_main_color.regex' => __('settings.branding_valid_main_color'),
            'branding_button_text_color.required' => __('settings.branding_valid_button_text_color'),
            'branding_button_text_color.regex' => __('settings.branding_valid_button_text_color'),
        ];
    }
}
